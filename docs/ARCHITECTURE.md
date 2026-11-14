# MAYELA — Audit initial & proposition d'architecture

> Plateforme intelligente de soutien scolaire du CE1 à la Terminale.
> Premier programme ciblé : République du Congo. Architecture multi-pays.
>
> Statut : **architecture validée**, phase 1 (socle) livrée. Voir §9 pour les décisions.
> Date : 14/11/2026.

*« Mayela » : clin d'œil au lingala* mayele *(intelligence, débrouillardise).
Le nom du produit est centralisé dans la configuration (`APP_NAME`, thème Flutter),
il pourra donc être changé sans refonte.*

---

## 0. Audit du projet existant

| Élément | Constat |
|---|---|
| Dépôt `Bweso88/Mayela` | **Vide** : aucun commit, aucune branche distante, aucun fichier. |
| `docs/ui-references/` | Absent : créé lors de cet audit avec les 9 captures Stitch fournies. |
| Travail existant à préserver | Aucun. |
| Outils disponibles dans l'environnement | PHP 8.3.6, Composer 2.8, PostgreSQL 16, Node 22, Docker 29. **Flutter/Dart absents** (à installer pour la phase 1). |

**Conséquence** : on part d'une page blanche. Les choix ci-dessous sont des
recommandations, pas des contraintes héritées.

### 0.1 Analyse des maquettes Stitch

Détail complet dans [`DESIGN_SYSTEM.md`](./DESIGN_SYSTEM.md). En résumé :

- **Base de design retenue** : les écrans « Mon Univers » / mascotte Léo
  (01 à 04) + les écrans « aventure » (05, 06). Même langage visuel : bleu
  marine profond, vert menthe, surfaces lavande, accents pêche et ambre,
  coins très arrondis, typographie sans-serif géométrique.
- **Inspiration lycée** : écrans 08 et 09 (tableau de bord et annales), plus
  dense et plus mature, même palette.
- **Écran 07** : rendu cassé (blocs vides, police serif de repli), à ne pas
  reproduire.
- Les marques visibles dans les maquettes (« EduState AI », « Safari Congo »)
  sont **remplacées par Mayela** et ses trois univers.

**Problèmes de fond repérés dans les maquettes** (en conflit avec le cahier des charges) :

| Maquette | Problème | Correction proposée |
|---|---|---|
| 04 « Diplôme Officiel… Certifié IA, validé selon le programme » | Présente un document généré comme officiel. | « Attestation de progression Mayela » : pas de mention « officiel », pas de « certifié IA ». |
| 09 « Banque officielle certifiée », « Validé Ministère », « Corrigé Certifié » | Ces affirmations sont interdites sauf validation réelle et traçable. | Badge affiché **seulement** si `exam_papers.source_verified_at` est renseigné, avec la source citée (ex. « Source : … — vérifié par l'équipe pédagogique »). |
| 09 « 68 % de succès, basé sur 4 230 entraînements » | Statistique fictive. | Calculée à partir des vraies tentatives, masquée sous un seuil minimal (ex. moins de 50 tentatives). |
| 04 « Progression optimale… sans blocage constaté » | Texte de diagnostic inventé. | Résumé généré à partir des données réelles, avec mention « résumé automatique ». |
| 08/09 « 14 jours consécutifs », flammes de série ; 05 « Défi Chrono 60 s » | Mécaniques de série et d'urgence qui poussent à l'usage excessif, à proscrire au primaire. | Série « douce » (jours actifs **par semaine**, sans perte punitive), pas de chrono au primaire par défaut, rappel de pause après 20 min. |
| 08 « Bac 2025 » | Daté. | Session dynamique selon l'année scolaire active. |

---

## 1. Vue d'ensemble

```
┌───────────────────────┐      ┌────────────────────────┐
│  App mobile Flutter   │      │  Back-office web       │
│  (Android / iOS)      │      │  (Filament, Laravel)   │
│  - Univers Primaire   │      │  admins, pédagogues,   │
│  - Univers Collège    │      │  contributeurs, modo   │
│  - Univers Lycée      │      └───────────┬────────────┘
│  - Espace Parent      │                  │ (même app Laravel,
│  - Cache hors ligne   │                  │  session web + 2FA)
└──────────┬────────────┘                  │
           │ HTTPS / JSON (API REST v1, Sanctum)
┌──────────▼──────────────────────────────▼─────────────┐
│               Backend Laravel 13 (PHP 8.3)            │
│        Monolithe modulaire : un module par domaine    │
│  Identity · Curriculum · Content · Learning ·         │
│  Competency(Adaptatif) · Gamification · Exams ·       │
│  Billing · Notifications · AI (couche fournisseurs)   │
└───┬──────────────┬──────────────┬──────────────┬──────┘
    │              │              │              │
┌───▼────┐   ┌─────▼─────┐  ┌─────▼──────┐  ┌────▼──────────────┐
│Postgres│   │  Redis    │  │ Stockage S3│  │ Fournisseurs IA   │
│16 +    │   │ cache,    │  │ médias,    │  │ (Claude, OpenAI,  │
│pgvector│   │ files,    │  │ annales PDF│  │  Mistral…) via    │
│        │   │ quotas    │  │ packs offl.│  │  adaptateurs      │
└────────┘   └───────────┘  └────────────┘  └───────────────────┘
```

### 1.1 Choix structurants (recommandations)

| Sujet | Choix | Pourquoi |
|---|---|---|
| Structure du dépôt | **Monorepo** : `backend/`, `mobile/`, `docs/` | Une seule source de vérité, API et app versionnées ensemble. |
| Backend | **Laravel 13**, PHP 8.3, monolithe modulaire | Proche de votre expérience PHP ; un monolithe bien découpé est plus simple à opérer que des microservices pour un MVP. |
| Base de données | **PostgreSQL 16** (+ `pgvector`) | JSONB pour les contenus de questions variés, recherche plein texte FR, vecteurs pour le tuteur IA (RAG) sans service externe. |
| Back-office | **Filament v5** dans l'app Laravel (validé) | CRUD, formulaires riches, imports, tableaux de bord en PHP pur : rapide à livrer et utilisable par une équipe non technique. |
| Auth mobile | **Laravel Sanctum** (tokens à capacités) | Simple, natif, révocable par appareil. |
| Rôles | **spatie/laravel-permission** | Standard de l'écosystème, gère rôles et permissions. |
| Files / jobs | Redis + **Horizon** | Appels IA, imports, notifications et calculs adaptatifs en asynchrone. |
| Doc API | **Scribe** (OpenAPI 3) | Générée depuis le code et les FormRequests. |
| Tests | **Pest** (backend), `flutter_test` + `integration_test` (mobile) | |
| Mobile | **Flutter 3.50**, Riverpod 3, go_router, Dio, Drift (SQLite, phase 3) | Architecture « feature-first », hors ligne natif. |
| IA | Module `AI` avec **interface `LlmProvider`** + adaptateurs, **désactivé par défaut** (`AI_PROVIDER=none`) | Pas de budget IA au lancement : tout fonctionne sans IA ; un fournisseur s'active par configuration le moment venu. |
| Moteur adaptatif | **Algorithme déterministe** (BKT simplifié / Elo), pas un LLM | Explicable, testable, peu coûteux. Le LLM explique, il ne note pas. |

---

## 2. Backend Laravel — organisation

```
backend/
├── app/
│   ├── Modules/
│   │   ├── Identity/        # users, profils, parent↔enfant, consentements
│   │   ├── Curriculum/      # pays, programmes, années, classes, séries, matières, trimestres, chapitres
│   │   ├── Content/         # leçons, objectifs, médias, versions, workflow de validation, imports
│   │   ├── Learning/        # exercices, questions, quiz, évaluations, tentatives, réponses
│   │   ├── Competency/      # compétences, maîtrise, diagnostic, parcours personnalisés
│   │   ├── Gamification/    # XP, étoiles, badges, missions, carte, défis
│   │   ├── Exams/           # examens nationaux, sessions, sujets, corrigés, examens blancs
│   │   ├── AI/              # LlmProvider, tuteur, garde-fous, quotas, journal
│   │   ├── Billing/         # plans, abonnements, paiements (Mobile Money plus tard)
│   │   └── Notifications/
│   │       └── (chaque module) Models/ Actions/ Http/{Controllers,Requests,Resources}/
│   │                           Policies/ Events/ Jobs/ Data/ routes.php
│   ├── Filament/            # back-office (Resources par module)
│   └── Support/             # transverses : ApiResponse, idempotence, audit
├── database/{migrations,seeders,factories}
├── tests/{Feature,Unit}
└── routes/api.php           # monte /api/v1/* depuis chaque module
```

**Conventions (SOLID)** :
- Contrôleurs fins → **Actions** (une classe = un cas d'usage, ex. `SubmitAttemptAction`).
- Validation dans les **FormRequests**, sortie via **API Resources** (format JSON stable).
- Autorisations dans les **Policies** (jamais dans les contrôleurs).
- Les modules communiquent par **interfaces et événements** (`AttemptSubmitted` → Competency, Gamification) et non par appels croisés aux modèles.
- Erreurs au format JSON standard de Laravel (`{message, errors}`), journalisation structurée avec `request_id`.

### 2.1 Couche IA (indépendante du fournisseur)

```php
interface LlmProvider {
    public function chat(ChatRequest $request): ChatResponse;      // messages, modèle, température, max tokens
    public function embed(array $texts): array;                    // pour le RAG
}
// Adaptateurs : AnthropicProvider, OpenAiProvider, MistralProvider, FakeProvider (tests)
// Choix par config : config/ai.php → 'default' => env('AI_PROVIDER'), modèle par usage.
```

Pipeline du **tuteur** (`TutorService`) :
1. **Contexte** : classe, série et âge de l'élève, chapitre en cours, compétences faibles.
2. **RAG** : recherche `pgvector` limitée aux contenus **publiés et validés** de *son* programme.
3. **Prompt système pédagogique** : méthode socratique (indices progressifs, pas la réponse d'emblée), langage adapté à l'âge, rester dans le programme, signaler l'incertitude.
4. **Garde-fous entrée/sortie** : filtre de modération, détection des données personnelles (masquage), refus hors sujet, longueur limitée pour les plus jeunes.
5. **Étiquetage** : toute réponse porte `source: "ai_generated"` et, le cas échéant, les liens vers les leçons validées utilisées. L'app affiche « Réponse de l'assistant IA, à vérifier dans ta leçon ».
6. **Quotas** (Redis) par plan et par jour, **journal** `ai_interactions` (tokens, coût, modèle, drapeaux de modération), consultable dans l'admin.
7. Pour les mineurs : le tuteur n'est activé qu'après **consentement parental** ; le parent voit l'historique.

### 2.2 Moteur de compétences et d'adaptation

- Chaque question est rattachée à **une ou plusieurs compétences** (`question_competency`) et a une difficulté (1 à 5).
- Après chaque réponse : mise à jour de `student_competencies.mastery` (0 à 1) par **BKT simplifié** (paramètres de devinette / d'erreur d'inattention par défaut, ajustables), et de `confidence` (selon le nombre d'observations récentes).
- Classement : `acquise` (≥ 0,8 et confiance suffisante), `partielle` (0,5 à 0,8), `non_maîtrisée` (< 0,5).
- **Diagnostic initial** : test court (10 à 20 questions) tiré des compétences clés de la classe, difficulté adaptée dynamiquement.
- **Parcours personnalisé** (`learning_paths`) : pour chaque compétence faible → leçon de rappel (prérequis via `competency_prerequisites`), exemple, exercices progressifs, puis réévaluation. Généré par règles ; le LLM ne sert qu'à rédiger les explications.
- **Difficulté adaptative** : sélection de la prochaine question dans une fenêtre visant environ 70 % de réussite.

---

## 3. Application Flutter — organisation

```
mobile/lib/
├── core/        # thème Mayela (3 univers), routing, client API Dio, erreurs, i18n, stockage sécurisé
├── data/        # base Drift locale, DTO, file de synchronisation
├── features/
│   ├── auth/            # connexion élève (code + PIN pour les jeunes), parent (email/téléphone)
│   ├── onboarding/      # pays → classe → série → diagnostic
│   ├── home_primary/    # « Mon Univers », carte d'aventure, missions
│   ├── home_secondary/  # tableau de bord collège / lycée
│   ├── courses/         # matières → trimestres → chapitres → leçons
│   ├── practice/        # exercices, quiz, évaluations (moteurs de types de questions)
│   ├── tutor/           # chat avec Léo / assistant (texte, voix plus tard)
│   ├── exams/           # annales, examens blancs chronométrés
│   ├── rewards/         # badges, étoiles, trophées
│   ├── parent/          # suivi multi-enfants, abonnement
│   └── settings/
└── main.dart
```

- **Thème par univers** : `MayelaTheme.primary()`, `.college()`, `.lycee()` choisi selon la classe (`grade.cycle`).
- **Hors ligne** : téléchargement de « packs chapitre » (JSON + images WebP compressées), stockés dans Drift ; les tentatives faites hors ligne vont dans une file avec **clé d'idempotence (UUID client)** puis sont synchronisées au retour du réseau (`POST /api/v1/sync/attempts`).
- **Données mobiles** : images WebP redimensionnées côté serveur, pagination, cache HTTP (ETag), option « Wi-Fi uniquement » pour les téléchargements.

---

## 4. Rôles, permissions et protection des mineurs

| Rôle | Accès principal |
|---|---|
| `student` | App mobile, ses données uniquement. |
| `parent` | App (espace parent) : enfants rattachés, abonnement, consentements. |
| `super_admin` | Tout, y compris la configuration, les tarifs et les utilisateurs. |
| `pedagogical_admin` | Programmes, contenus, **validation/publication**, annales. |
| `contributor` (enseignant) | Création de brouillons de contenus, **sans publication**. |
| `moderator` | Signalements, journaux IA, contenus soumis. |

Mineurs :
- Compte élève de **moins de 15 ans créé ou validé par un parent** (consentement horodaté dans `consents`).
- Données minimales : pas de photo réelle obligatoire (avatars), établissement facultatif, pas de géolocalisation, pas de messagerie entre utilisateurs.
- Contrôle parental : temps quotidien max, activation du tuteur IA, plages horaires.
- Rappels de pause, sessions courtes (5 à 15 min) au primaire, aucune notification push la nuit.
- Droit d'accès et de suppression (export / suppression du compte depuis l'espace parent).

Sécurité :
- Mots de passe Argon2id, 2FA obligatoire pour les rôles admin, tokens Sanctum par appareil avec expiration.
- Rate limiting par route (auth, IA, soumissions), CORS strict, en-têtes de sécurité.
- Audit (`spatie/laravel-activitylog`) des actions admin, publication et paiement.
- Sauvegardes Postgres quotidiennes chiffrées, restauration testée.
- Secrets hors dépôt, dépendances surveillées (`composer audit`, Dependabot).

---

## 5. Schéma initial de la base de données

Conventions : PK `bigint` auto-incrémentée + `uuid` public pour les ressources
exposées à l'API, `timestamps`, `softDeletes` sur les contenus, FK avec
`ON DELETE` explicite, index sur toutes les FK et colonnes filtrées.

### 5.1 Identité

| Table | Colonnes clés |
|---|---|
| `users` | id, uuid, name, email (nullable, unique), phone (nullable, unique), password, locale, status, last_login_at |
| `student_profiles` | id, user_id (FK unique), first_name, last_name, birth_date, country_id, school_name?, grade_id, school_series_id?, academic_year_id, level, xp, avatar, login_code (unique, pour les jeunes), pin_hash |
| `parent_profiles` | id, user_id, phone, relationship |
| `parent_student` | parent_profile_id, student_profile_id, is_primary, verified_at — PK composite |
| `consents` | id, parent_profile_id, student_profile_id, type (account, ai_tutor, notifications…), granted_at, revoked_at, ip, version |
| `parental_controls` | student_profile_id, daily_minutes_limit, ai_tutor_enabled, quiet_hours |
| `roles`, `permissions`, … | (spatie/laravel-permission) |
| `devices` | id, user_id, platform, push_token, last_seen_at |

### 5.2 Programme scolaire (implémenté en phase 2a)

Deux niveaux distincts :

1. **La structure du système éducatif**, stable d'une année sur l'autre. Celle du Congo a été fournie par l'équipe le 14/11/2026 et chargée par `CongoEducationStructureSeeder`.
2. **Le programme**, versionné par année scolaire (matières, chapitres).

```
countries
 ├─< education_stages      Préscolaire · Primaire · Secondaire général · Secondaire technique
 │    ├─< education_cycles  Collège · Lycée (facultatif)
 │    ├─< grades            P1…P3 · CP1 · CP2 · CE1…CM2 · 6e…3e · Seconde · Première · Terminale
 │    └─< tracks            Séries A, B, C, D · Spécialités Industriel, Commercial, Agricole
 │         grade_track       Seconde : A, C · Première : A, B, C, D · Terminale : A, B, C, D
 └─< academic_years ─< terms (trimestres)
      └── curricula (1 par année ; publication = référence officielle obligatoire)
           └─< subjects (par classe ; subject_track = séries concernées + coefficient)
                └─< chapters (rattachés à un trimestre ; statut, is_free, is_demo)
```

| Table | Colonnes clés |
|---|---|
| `education_stages` | country_id, code, name, position |
| `education_cycles` | education_stage_id, code, name, position |
| `grades` | education_stage_id, education_cycle_id?, code, name, position, **universe** (junior/college/lycee), **is_available** (proposée dans l'app) |
| `tracks` | education_stage_id, kind (series/specialty), code, name |
| `grade_track` | grade_id, track_id |
| `academic_years` | country_id, label, starts_on, ends_on, is_current (une seule par pays) |
| `terms` | academic_year_id, number, name, dates |
| `curricula` | academic_year_id (unique), name, official_reference, status, published_at, published_by |
| `subjects` | curriculum_id, grade_id, code, name, color, icon, position |
| `subject_track` | subject_id, track_id, coefficient. Sans ligne, la matière est commune à toutes les séries |
| `chapters` | uuid, subject_id, term_id?, title, summary, position, status, is_free, is_demo |
| `student_profiles` (+) | grade_id, track_id |

- **Périmètre de l'application** : CE1 → Terminale (`is_available`). Préscolaire, CP1 et CP2 sont dans la structure mais pas proposés aux élèves.
- **Secondaire technique** : seules les spécialités sont chargées. Les classes et les « autres spécialités » se complètent dans le back-office quand l'équipe les fournira.

### 5.3 Contenus pédagogiques et validation

| Table | Colonnes clés |
|---|---|
| `lessons` | id, chapter_id, title, body (JSON blocs : texte, encadré « formule », exemple, image, audio), order, audio_url, is_demo |
| `learning_objectives` | id, chapter_id, description, order |
| `competencies` | id, subject_id, code, title, description, grade_id |
| `competency_prerequisites` | competency_id, prerequisite_id |
| `objective_competency` | learning_objective_id, competency_id |
| `media` | (spatie/medialibrary) |
| `content_versions` | id, versionable_type, versionable_id, version, payload (JSONB), status (draft/in_review/published/archived), author_id, reviewer_id, reviewed_at, review_notes |
| `content_imports` | id, type (csv/xlsx/docx), file_path, status, report (JSONB), imported_by |

Champ **`is_demo`** sur les contenus : les données de démonstration sont
étiquetées « DÉMO » partout (API et app) tant que les contenus officiels ne sont pas importés.

### 5.4 Apprentissage et évaluation

| Table | Colonnes clés |
|---|---|
| `exercises` | id, chapter_id, title, difficulty, type (practice/challenge/minigame) |
| `questions` | id, questionable (exercise/quiz/assessment, morphique), type (mcq, multi, true_false, fill_blank, numeric, matching, ordering, open), prompt, payload (JSONB), explanation, difficulty, points |
| `answers` | id, question_id, label, is_correct, feedback, order |
| `question_competency` | question_id, competency_id, weight |
| `quizzes` | id, chapter_id, title, pass_threshold, unlocks_next_chapter |
| `assessments` | id, scope (diagnostic/chapter/term/mock_exam), grade_id, subject_id?, duration_seconds?, config (JSONB) |
| `assessment_attempts` | id, uuid (idempotence client), student_profile_id, attemptable (quiz/assessment/exercise), started_at, submitted_at, score, max_score, duration_seconds, source (online/offline) |
| `student_responses` | id, attempt_id, question_id, response (JSONB), is_correct, time_spent_ms, hints_used |
| `chapter_progress` | student_profile_id, chapter_id, status (locked/available/in_progress/completed), stars (0-3), best_score, completed_at |

### 5.5 Compétences et parcours

| Table | Colonnes clés |
|---|---|
| `student_competencies` | student_profile_id, competency_id, mastery (0-1), confidence (0-1), state (acquired/partial/not_acquired), observations, updated_at |
| `competency_assessments` | id, student_profile_id, competency_id, attempt_id, mastery_before, mastery_after |
| `learning_paths` | id, student_profile_id, reason (diagnostic/remediation/goal), status, generated_by (rules/ai), created_at |
| `learning_path_items` | id, learning_path_id, itemable (lesson/exercise/quiz), order, status, completed_at |
| `weekly_goals` | student_profile_id, week_start, target, progress |

### 5.6 IA

| Table | Colonnes clés |
|---|---|
| `ai_conversations` | id, student_profile_id, chapter_id?, started_at |
| `ai_interactions` | id, conversation_id, role, content (chiffré), provider, model, tokens_in, tokens_out, cost_micros, moderation_flags (JSONB), sources (JSONB), latency_ms |
| `content_embeddings` | id, embeddable_type/id, chunk, embedding (vector), curriculum_id, grade_id |
| `ai_quotas` | plan_id, feature, daily_limit |

### 5.7 Gamification

| Table | Colonnes clés |
|---|---|
| `badges` | id, code, name, description, icon, criteria (JSONB), cycle |
| `student_rewards` | id, student_profile_id, rewardable (badge/avatar_item), awarded_at |
| `xp_ledger` | id, student_profile_id, amount, reason, source_type/id, created_at (journal append-only) |
| `missions` | id, grade_id, title, type (daily/chapter), criteria (JSONB), xp_reward, active_from/to |
| `student_missions` | student_profile_id, mission_id, progress, completed_at |
| `worlds` | id, subject_id, name, theme, order — monde de la carte = matière ; niveau = chapitre |

### 5.8 Examens nationaux

| Table | Colonnes clés |
|---|---|
| `exams` | id, country_id, code (CEPE, BEPC, BAC…), name, grade_id |
| `exam_sessions` | id, exam_id, year, session_type (normale/remplacement), held_on |
| `exam_subjects` | id, exam_session_id, subject_id, school_series_id?, duration_minutes, coefficient |
| `exam_papers` | id, exam_subject_id, file_path, source_name, source_url, **source_verified_at**, verified_by, status, download_allowed |
| `exam_solutions` | id, exam_paper_id, type (official/pedagogical_team), file_path, body, verified_at, verified_by |
| `mock_exam_attempts` | via `assessment_attempts` (attemptable = exam_subject) + timer côté serveur (`started_at`, `deadline_at`) |

### 5.9 Abonnements, paiements, notifications

| Table | Colonnes clés |
|---|---|
| `plans` | id, code (free, premium), name, features (JSONB), country_id |
| `plan_prices` | id, plan_id, period (monthly/quarterly/yearly), amount, currency (XAF), is_active |
| `subscriptions` | id, parent_profile_id (ou user payeur), plan_price_id, status, starts_at, ends_at, auto_renew |
| `subscription_student` | subscription_id, student_profile_id |
| `payments` | id, subscription_id, provider (mtn_momo, airtel_money, manual…), provider_ref (unique), amount, currency, status, payload (JSONB), paid_at |
| `notifications` | (table native Laravel) |
| `activity_log` | (spatie/activitylog) |

---

## 6. API REST v1 (extrait)

```
POST   /api/v1/auth/register-parent | /auth/login | /auth/student-login (code + PIN) | /auth/logout
GET    /api/v1/me
POST   /api/v1/parent/children            GET /parent/children/{id}/report
GET    /api/v1/catalog/countries → /curricula/{id}/grades → /grades/{id}/subjects
GET    /api/v1/subjects/{id}/terms        GET /chapters/{id}   GET /lessons/{id}
GET    /api/v1/chapters/{id}/pack         (pack hors ligne)
POST   /api/v1/attempts                   POST /attempts/{uuid}/submit   POST /sync/attempts
GET    /api/v1/me/progress | /me/competencies | /me/learning-path | /me/dashboard
GET    /api/v1/me/rewards | /missions/today | /map
POST   /api/v1/tutor/conversations        POST /tutor/conversations/{id}/messages
GET    /api/v1/exams?country=CG           GET /exam-sessions/{id}/subjects   POST /exam-subjects/{id}/mock
GET    /api/v1/plans                      POST /subscriptions
```

Versionnement par préfixe `/v1`, pagination par curseur, réponses `JsonResource`, doc Scribe sur `/docs`.

---

## 7. Plan d'exécution

| Phase | Livrables | Critère de « terminé » |
|---|---|---|
| **1. Socle** | Monorepo, Laravel 13 + Postgres + Redis (Docker Compose), Sanctum, rôles et permissions, Filament (connexion admin + 2FA, gestion des utilisateurs), squelette Flutter (thème Mayela à 3 univers, routing, client API, écran de connexion), CI GitHub Actions (Pest, Pint, `composer audit`, `flutter analyze` / `flutter test`) | Connexion admin et élève fonctionnelle de bout en bout, tests verts en CI |
| **2. Pédagogie** | Migrations 5.2 à 5.4, CRUD Filament de toute la hiérarchie, éditeur de leçon par blocs, workflow brouillon → relecture → publication, versions, import CSV/Excel, **import de cours scannés (OCR → brouillon → validation)**, seeders **DÉMO** étiquetés | Un pédagogue non technique crée, fait valider et publie un chapitre complet |
| **3. App élève** | Inscription parent + enfant, choix de classe/série, tableau de bord, cours, moteur de questions (QCM, numérique, texte à trous…), progression, packs hors ligne et synchronisation | Un élève suit un chapitre et réalise un quiz, y compris hors ligne |
| **4. Gamification** | Carte d'aventure primaire, XP (registre), étoiles, badges, missions du jour, déblocage des chapitres, pauses | Chapitre validé → étape suivante débloquée + récompense |
| **5. IA** | Compétences, diagnostic, BKT, parcours personnalisé, tuteur RAG + garde-fous + quotas + journal admin | Diagnostic → parcours généré ; tuteur qui refuse de donner directement la réponse (tests) |
| **6. Examens** | Exams/sessions/sujets/corrigés, import et vérification des sources, examens blancs chronométrés (timer serveur), analyse des erreurs → révisions | Examen blanc complet noté, erreurs reliées aux compétences |
| **7. Parents & monétisation** | Espace parent multi-enfants, plans et tarifs configurables, droits freemium, interface `PaymentGateway` (paiement manuel puis MTN MoMo / Airtel Money), notifications | Abonnement activé ↔ accès premium |
| **8. Tests & déploiement** | Tests de sécurité (OWASP ASVS niveau 2), de charge (k6), sur appareils Android d'entrée de gamme et iOS, déploiement (VPS + Docker, Forge ou équivalent), sauvegardes, supervision (Sentry) | Mise en production pilote |

### Phase 1 : fichiers qui seront créés

```
docker-compose.yml                     # postgres, redis, mailpit
.github/workflows/{backend,mobile}.yml
backend/ (laravel new)                 # + sanctum, spatie/permission, filament, scribe, pest, pint (Larastan : à ajouter, voir README)
  app/Modules/Identity/...             # modèles User, StudentProfile, ParentProfile, Actions d'auth, Policies
  database/migrations/*_identity_*     # users, student_profiles, parent_profiles, parent_student, consents, devices
  database/seeders/RolesAndPermissionsSeeder.php
  app/Filament/Resources/UserResource.php
  tests/Feature/Auth/*                 # inscription parent, connexion élève, accès refusés selon le rôle
mobile/ (flutter create)
  lib/core/theme/mayela_theme.dart     # tokens issus de DESIGN_SYSTEM.md
  lib/core/api/, lib/core/router/
  lib/features/auth/
README.md
```

---

## 8. Ce qui n'est **pas** inventé, et comment

- **Programmes, séries, coefficients et chapitres officiels** : saisis ou importés par un administrateur pédagogique à partir des textes officiels du MEPSA / du ministère congolais, avec référence de la source (`curricula.official_reference`) et validation obligatoire avant publication.
- **Annales et corrigés** : importés depuis des sources fiables, champs `source_name`, `source_url`, `source_verified_at`, `verified_by` obligatoires pour l'affichage ; un corrigé rédigé par l'équipe est étiqueté « corrigé proposé par l'équipe pédagogique », jamais « officiel ».
- **Données de démo** : drapeau `is_demo`, mention « DÉMO » visible.

## 9. Décisions validées (14/11/2026)

| # | Sujet | Décision | Conséquence technique |
|---|---|---|---|
| 1 | Back-office | **Filament** | Livré en phase 1 (v5), 2FA obligatoire pour le personnel. |
| 2 | IA | **Pas de budget pour l'instant** | Module IA désactivé par défaut (`AI_PROVIDER=none`) : l'interface du tuteur est **masquée**, pas simulée. Le diagnostic et les parcours personnalisés reposent sur le moteur de règles (BKT), qui ne coûte rien. Options futures sans gros budget : quotas gratuits de fournisseurs (à vérifier pour les données de mineurs) ou modèle open source auto-hébergé. |
| 3 | Hébergement | « Comme Scolia » | **À préciser** (voir ci-dessous). En attendant : tout est conteneurisable (Docker), donc portable vers n'importe quel hébergeur ; cible économique d'un seul VPS au démarrage. |
| 4 | Majorité numérique | **15 ans** | Implémenté : en dessous, compte créé par un parent avec consentement horodaté ; à partir de 15 ans, inscription autonome. |
| 5 | Contenus | **Cours scannés par l'équipe** | Pipeline d'import en phase 2, décrit en §9.1. |

**Point ouvert, l'hébergement** : je n'ai pas trouvé de référence publique à « Scolia ». Merci de préciser
de quoi il s'agit (un site, une application, l'hébergeur utilisé), pour reproduire le même choix.

### 9.1 Import des cours scannés (phase 2)

```
Scan (PDF / photos) ──► upload back-office ──► OCR Tesseract (français, gratuit, sur le serveur)
      ──► découpage en brouillon de leçon (texte + images recadrées) ──► correction dans l'éditeur
      ──► relecture par un admin pédagogique ──► publication (versionnée)
```

- Le scan original est conservé et rattaché au chapitre (traçabilité de la source).
- Un contenu issu de l'OCR reste **brouillon** tant qu'il n'est pas relu : rien n'est publié automatiquement.
- Conseils de numérisation : 300 dpi, page à plat, bonne lumière (fiche fournie dans le back-office).
- **Droits d'auteur** : ne numériser que des contenus dont Mayela a les droits (cours rédigés par l'équipe,
  manuels sous licence ou avec autorisation de l'éditeur).

## 10. État d'avancement

| Phase | Statut |
|---|---|
| 0. Audit et architecture | ✅ |
| 1. Socle technique | ✅ Backend (identité, rôles, API d'authentification, back-office, 40 tests) et app mobile (thème, navigation, connexions, espace parent, 17 tests) |
| 2a. Structure et programme | ✅ Structure du Congo, années, trimestres, programmes, matières, chapitres (back-office + API), classe/série de l'élève (API + app) |
| 2b. Contenus | ⏭️ Leçons, objectifs, exemples, exercices, quiz, versions, import CSV/Excel et cours scannés |
