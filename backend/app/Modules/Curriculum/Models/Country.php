<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Racine de la hiérarchie pédagogique : PAYS → PROGRAMME → ANNÉE → CLASSE → … */
#[Fillable(['code', 'name', 'default_locale', 'currency', 'is_active'])]
class Country extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
