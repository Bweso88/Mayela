<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Ordre d'enseignement : préscolaire, primaire, secondaire général, secondaire technique. */
#[Fillable(['country_id', 'code', 'name', 'position'])]
class EducationStage extends Model
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(EducationCycle::class)->orderBy('position');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class)->orderBy('position');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('position');
    }
}
