<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['subject_id', 'term_id', 'title', 'summary', 'position', 'status', 'is_free', 'is_demo'])]
class Chapter extends Model
{
    use HasUuids, SoftDeletes;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'is_free' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published);
    }
}
