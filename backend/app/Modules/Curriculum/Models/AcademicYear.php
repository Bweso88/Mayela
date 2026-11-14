<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['country_id', 'label', 'starts_on', 'ends_on', 'is_current'])]
class AcademicYear extends Model
{
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Une seule année « en cours » par pays.
        static::saved(function (AcademicYear $year) {
            if ($year->is_current && ($year->wasRecentlyCreated || $year->wasChanged('is_current'))) {
                // Requête Eloquent sans événements : pas de récursion.
                static::query()
                    ->where('country_id', $year->country_id)
                    ->whereKeyNot($year->getKey())
                    ->update(['is_current' => false]);
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('number');
    }

    public function curriculum(): HasOne
    {
        return $this->hasOne(Curriculum::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
