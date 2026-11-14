# Mayela — consignes pour les agents

Plateforme de soutien scolaire (CE1 → Terminale), monorepo : `backend/` (Laravel 13, Filament v5, PostgreSQL), `mobile/` (Flutter 3.50, Riverpod 3, go_router). Lire `docs/ARCHITECTURE.md` avant toute évolution.

## Règles produit (non négociables)
- Ne jamais inventer de programmes scolaires, sujets ou corrigés officiels. Données de démo : drapeau `is_demo` + mention « DÉMO ».
- Aucune mention « officiel », « certifié », « validé ministère » sans données de vérification en base.
- Pas de bouton factice ni de fonctionnalité simulée. L'IA est désactivée par défaut : on masque l'interface, on ne la simule pas.
- Mineurs : moins de 15 ans = compte créé par un parent avec consentement (`consents`). Pas de séries punitives ni de chrono au primaire.

## Backend
- Code par module : `app/Modules/<Module>/{Models,Actions,Http,Policies,Enums,routes}`. Contrôleurs fins → Actions ; validation en FormRequest ; sortie en API Resource ; autorisations en Policy (enregistrées dans `AppServiceProvider`).
- Routes de module montées dans `routes/api.php` sous `/api/v1`.
- Tests : `cd backend && vendor/bin/pest` (PostgreSQL `mayela_test`) ; style : `vendor/bin/pint`.
- Composer derrière un proxy : utiliser `--prefer-source` si les archives GitHub sont refusées.

## Mobile
- Material via `package:material_ui/material_ui.dart` (et non `flutter/material.dart`) ; `package:flutter/services.dart` pour les services.
- `cd mobile && flutter analyze && flutter test` ; format : `dart format -l 120 lib test`.
