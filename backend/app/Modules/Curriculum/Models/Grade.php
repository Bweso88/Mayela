<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\Universe;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['education_stage_id', 'education_cycle_id', 'code', 'name', 'position', 'universe', 'is_available'])]
class Grade extends Model
{
    protected function casts(): array
    {
        return [
            'universe' => Universe::class,
            'is_available' => 'boolean',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationStage::class, 'education_stage_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(EducationCycle::class, 'education_cycle_id');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)->orderBy('position');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('position');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function requiresTrack(): bool
    {
        return $this->tracks()->exists();
    }

    /** Nom complet pour les listes : « Première (Lycée) ». */
    public function getFullNameAttribute(): string
    {
        $context = $this->cycle?->name ?? $this->stage?->name;

        return $context ? "{$this->name} ({$context})" : $this->name;
    }
}
