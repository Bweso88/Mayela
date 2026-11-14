<?php

namespace App\Modules\Curriculum\Enums;

use Filament\Support\Contracts\HasLabel;

/** Univers visuel de l'application mobile, déterminé par la classe. */
enum Universe: string implements HasLabel
{
    case Junior = 'junior';
    case College = 'college';
    case Lycee = 'lycee';

    public function getLabel(): string
    {
        return match ($this) {
            self::Junior => 'Junior (gamifié)',
            self::College => 'Collège',
            self::Lycee => 'Lycée',
        };
    }
}
