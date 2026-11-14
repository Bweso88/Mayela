# Mayela

Plateforme intelligente de soutien scolaire, du CE1 à la Terminale.
Premier programme ciblé : République du Congo (architecture multi-pays).

> **Statut : étape 0, audit et architecture.** Aucun code applicatif pour l'instant.

## Documentation

- [Architecture technique, schéma de base de données et plan par phases](docs/ARCHITECTURE.md)
- [Design system issu des maquettes Stitch](docs/DESIGN_SYSTEM.md)
- [Maquettes de référence](docs/ui-references/)

## Stack prévue

| Couche | Technologie |
|---|---|
| Mobile | Flutter (Android / iOS), Riverpod, Drift (hors ligne) |
| Backend / API | Laravel 12, PHP 8.3, API REST v1, Sanctum |
| Back-office | Filament (dans l'app Laravel) |
| Base de données | PostgreSQL 16 + pgvector, Redis |
| IA | Couche `LlmProvider` indépendante du fournisseur |
