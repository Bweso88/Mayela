<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Trimestre d'une année scolaire. */
#[Fillable(['academic_year_id', 'number', 'name', 'starts_on', 'ends_on'])]
class Term extends Model
{
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
