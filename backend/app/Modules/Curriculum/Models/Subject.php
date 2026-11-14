<?php

namespace App\Modules\Curriculum\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['curriculum_id', 'grade_id', 'code', 'name', 'color', 'icon', 'position'])]
class Subject extends Model
{
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /** Séries concernées ; aucune = matière commune à toutes les séries de la classe. */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)->withPivot('coefficient');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('position');
    }

    /** Matières visibles pour une classe et, le cas échéant, une série. */
    public function scopeForStudent(Builder $query, int $gradeId, ?int $trackId): Builder
    {
        return $query->where('grade_id', $gradeId)
            ->where(fn (Builder $q) => $q->whereDoesntHave('tracks')
                ->when($trackId, fn (Builder $q) => $q->orWhereHas('tracks', fn (Builder $t) => $t->whereKey($trackId))));
    }
}
