<?php

namespace App\Modules\Identity\Enums;

enum ConsentType: string
{
    /** Création et utilisation du compte élève, traitement des données scolaires. */
    case Account = 'account';
    /** Utilisation de l'assistant pédagogique IA. */
    case AiTutor = 'ai_tutor';
}
