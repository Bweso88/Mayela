<?php

namespace App\Modules\Curriculum\Enums;

use Filament\Support\Contracts\HasLabel;

enum TrackKind: string implements HasLabel
{
    /** Série du secondaire général (A, B, C, D…). */
    case Series = 'series';
    /** Filière / spécialité du secondaire technique. */
    case Specialty = 'specialty';

    public function getLabel(): string
    {
        return match ($this) {
            self::Series => 'Série',
            self::Specialty => 'Spécialité',
        };
    }
}
