<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\TrackKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Série (A, B, C, D) ou spécialité du technique. Une même série couvre plusieurs classes. */
#[Fillable(['education_stage_id', 'kind', 'code', 'name', 'position'])]
class Track extends Model
{
    protected function casts(): array
    {
        return ['kind' => TrackKind::class];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationStage::class, 'education_stage_id');
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class)->orderBy('position');
    }
}
