<?php

namespace App\Modules\Curriculum\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** Cycle de vie d'un programme ou d'un contenu pédagogique. */
enum PublicationStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::InReview => 'En relecture',
            self::Published => 'Publié',
            self::Archived => 'Archivé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InReview => 'warning',
            self::Published => 'success',
            self::Archived => 'danger',
        };
    }
}
