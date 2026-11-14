<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Cycle à l'intérieur d'un ordre (ex. collège, lycée). */
#[Fillable(['education_stage_id', 'code', 'name', 'position'])]
class EducationCycle extends Model
{
    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationStage::class, 'education_stage_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class)->orderBy('position');
    }
}
