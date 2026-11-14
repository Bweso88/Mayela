<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id'])]
class ParentProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(StudentProfile::class, 'parent_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function isParentOf(StudentProfile $student): bool
    {
        return $this->children()->whereKey($student->getKey())->exists();
    }
}
