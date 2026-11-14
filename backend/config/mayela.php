<?php

return [

    /*
    | Âge à partir duquel un élève peut créer son compte et consentir seul
    | (majorité numérique). En dessous, un parent ou tuteur est obligatoire.
    */
    'digital_majority_age' => (int) env('MAYELA_DIGITAL_MAJORITY_AGE', 15),

    // Âges acceptés pour un profil élève (CE1 → Terminale, avec marge).
    'student_min_age' => 5,
    'student_max_age' => 25,

    // Version des conditions / politique de confidentialité acceptées.
    'consent_version' => env('MAYELA_CONSENT_VERSION', '2026-11'),

    'student_login' => [
        'code_prefix' => 'MY',
        'code_length' => 6,
        'max_attempts_per_minute' => 5,
    ],

    // Durée de vie des jetons API mobiles (minutes). Null = pas d'expiration.
    'token_ttl_minutes' => (int) env('MAYELA_TOKEN_TTL_MINUTES', 60 * 24 * 30),

    // Compte super-admin créé par le seeder (à changer en production).
    'bootstrap_admin' => [
        'email' => env('MAYELA_ADMIN_EMAIL'),
        'password' => env('MAYELA_ADMIN_PASSWORD'),
    ],
];
