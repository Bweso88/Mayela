<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Programme scolaire d'une année (versionnement du contenu par année). */
#[Fillable(['academic_year_id', 'name', 'official_reference', 'status'])]
class Curriculum extends Model
{
    protected $table = 'curricula';

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Horodate la publication et son auteur (traçabilité).
        static::saving(function (Curriculum $curriculum) {
            if ($curriculum->isDirty('status') && $curriculum->status === PublicationStatus::Published) {
                $curriculum->published_at = now();
                $curriculum->published_by = auth()->id();
            }
        });
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('position');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === PublicationStatus::Published;
    }
}
