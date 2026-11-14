<?php

namespace App\Modules\Identity\Models;

use App\Modules\Curriculum\Models\Country;
use App\Modules\Curriculum\Models\Grade;
use App\Modules\Curriculum\Models\Track;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'first_name', 'last_name', 'birth_date', 'country_id', 'grade_id', 'track_id', 'school_name', 'login_code', 'pin_hash', 'avatar'])]
#[Hidden(['pin_hash'])]
class StudentProfile extends Model
{
    use HasUuids, SoftDeletes;

    /** Relations nécessaires à StudentProfileResource (évite le lazy loading). */
    public const API_RELATIONS = ['country', 'grade', 'track'];

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
            'birth_date' => 'date',
            'level' => 'integer',
            'xp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentProfile::class, 'parent_student')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    public function age(): int
    {
        return $this->birth_date->age;
    }

    public function isMinorForConsent(): bool
    {
        return $this->age() < config('mayela.digital_majority_age');
    }
}
