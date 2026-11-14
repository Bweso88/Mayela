# MAYELA — Design system (issu des maquettes Stitch)

Références : [`docs/ui-references/`](./ui-references/).
Couleurs mesurées directement sur les captures, à affiner avec les fichiers Stitch sources.

## 1. Inventaire des maquettes

| Fichier | Écran | Usage |
|---|---|---|
| 01-primaire-accueil-mon-univers | Accueil primaire « Mon Univers », Léo, mission du jour, matières | **Base** |
| 02-primaire-lecon-accords | Leçon (règle, pièges, fiche mémo, mini-jeux, quiz) | **Base** |
| 03-primaire-session-exercice | Exercice QCM illustré, feedback, explication, badge | **Base** |
| 04-primaire-trophees-espace-parent | Niveau, médailles, récompenses, résumé parent | **Base** (voir corrections) |
| 05-primaire-carte-aventure-desktop | Carte d'aventure, étapes = chapitres, boutique | **Base** (tablette / web) |
| 06-primaire-defi-calcul-desktop | Défi calcul contextualisé (cauris, safous) | **Base** (tablette / web) |
| 07-primaire-defi-mobile-rendu-casse | Rendu raté (blocs vides, police serif) | **Ne pas reproduire** |
| 08-lycee-accueil | Tableau de bord lycée, diagnostic IA, programme du jour | Inspiration lycée |
| 09-lycee-annales | Annales et corrigés, filtres, examens blancs | Inspiration lycée (voir corrections) |

Les noms « EduState AI » et « Safari Congo » visibles sur les maquettes sont remplacés par **Mayela**.

## 2. Tokens de couleur

| Token | Valeur | Rôle |
|---|---|---|
| `primary` | `#00236F` | Bleu marine : titres, CTA principal, cartes héros |
| `onPrimary` | `#FFFFFF` | |
| `secondary` | `#006C49` | Vert profond : CTA « Jouer », succès, validation |
| `secondaryContainer` | `#6FFBBE` | Vert menthe : bonne réponse, badges « Débloqué », chips |
| `onSecondaryContainer` | `#003F25` | |
| `surface` | `#FAF8FF` | Fond d'écran |
| `surfaceContainer` | `#E2E7FF` | Lavande : boutons secondaires, encadrés, bulles de Léo |
| `card` | `#FFFFFF` | Cartes |
| `tertiaryContainer` | `#FFDDB8` | Pêche : étoiles / récompenses, alertes douces |
| `star` | `#F5A524` | Étoiles, XP |
| `error` | `#BA1A1A` / container `#FFDAD6` | Mauvaise réponse (toujours accompagnée d'une explication, jamais punitive) |
| `textPrimary` | `#0B1A4A` | Texte principal |
| `textSecondary` | `#4A5068` | Texte secondaire |

Contraste : texte sur menthe en `#003F25` et sur pêche en `#5A3A00` (AA ≥ 4,5:1).

## 3. Typographie

Sans-serif géométrique arrondie, proche des maquettes : **Plus Jakarta Sans**
(titres) + **Lexend** (texte, conçue pour la lisibilité et les lecteurs débutants).
Polices embarquées dans l'app (fonctionne hors ligne, pas de téléchargement à la volée).

| Style | Primaire | Collège / Lycée |
|---|---|---|
| Display | 28 / 700 | 26 / 700 |
| Titre carte | 20 / 700 | 18 / 600 |
| Corps | **17** / 400, interligne 1,5 | 15 / 400 |
| Libellé | 14 / 600 | 13 / 600 |

## 4. Formes, espacements, élévation

- Rayons : cartes 20 px, boutons 16 px, chips en pilule.
- Grille de 8 px ; marges écran de 16 px ; espacement entre cartes de 16 px.
- Ombres très légères (`0 2 8 rgba(0,35,111,.06)`), la profondeur vient surtout des surfaces colorées.
- Cibles tactiles ≥ 48 px (≥ 56 px pour les réponses au primaire).

## 5. Trois univers visuels

| | **Mayela Junior** (CE1 à CM2) | **Mayela Collège** (6e à 3e) | **Mayela Lycée** (2nde à Tle) |
|---|---|---|---|
| Ton | Aventure, mascotte **Léo le lionceau**, personnages | Encourageant, plus sobre | Mature, orienté examens (BAC) |
| Navigation | 4 onglets : Mon Univers, Mes Leçons, Léo (tuteur), Mes Trophées | Accueil, Cours, Exercices, Tuteur, Profil | Accueil, Mes Cours, Tuteur IA, Examens |
| Accueil | Carte d'aventure, missions, étoiles | Progression par matière, objectifs de la semaine | Diagnostic par matière, programme du jour, annales |
| Densité | Faible, gros éléments, illustrations | Moyenne | Plus forte, chiffres et graphiques |
| Couleur dominante | Menthe + pêche sur lavande | Équilibre marine / menthe | Marine dominant, menthe en accent |
| Audio | « Écouter Léo » sur chaque consigne | Optionnel | Non |
| Chrono | **Non** par défaut | Optionnel | Examens blancs |

## 6. Composants récurrents (à implémenter en widgets Flutter)

`MayelaAppBar` (classe + étoiles + profil), `HeroGreetingCard` (Léo + bulle + écouter),
`ProgressCard` (niveau, barre, objectif), `MissionCard`, `SubjectCard` (icône, étoiles, statut, action),
`LessonRuleBlock` (encadré « formule secrète »), `ExampleTransform` (singulier → pluriel),
`TrapBlock` (encadré pêche), `MemoCard`, `AnswerOption` (A/B/C, états neutre / sélectionné / correct / incorrect),
`FeedbackPanel` (explication de Léo), `BadgeTile` (débloqué / en cours / verrouillé),
`MapStep` (réussi / actuel / verrouillé / coffre / final), `ParentSummaryCard`,
`ExamPaperCard` (année, série, matière, sujet / corrigé, statut de vérification), `AiDisclaimer`.

## 7. Règles UX imposées par le cahier des charges

1. Aucun bouton factice : un composant sans fonctionnalité réelle n'est pas affiché.
2. Mentions « officiel », « certifié », « validé ministère » : **uniquement** si les données de vérification existent (voir ARCHITECTURE §0.1).
3. Contenu IA toujours signalé (`AiDisclaimer`).
4. Pas de séries punitives ni de compte à rebours au primaire ; rappel de pause après 20 min ; objectifs **hebdomadaires**.
5. Mauvaise réponse = explication + nouvel essai, jamais de perte de vies au primaire (les « cœurs/énergie » de la maquette 05 sont abandonnés).
6. Accessibilité : tailles de texte dynamiques, lecteurs d'écran (Semantics), pas d'information portée par la couleur seule.
7. Contextualisation locale bienvenue (cauris, safous, fleuve Congo, Odzala), avec des contenus validés par l'équipe pédagogique.
