<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'locale', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, LogsActivity, Notifiable, SoftDeletes;

    use InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery;

    /** Le rôle spatie est rattaché au guard web, y compris pour les appels API (Sanctum). */
    protected string $guard_name = 'web';

    /** Valeurs par défaut côté modèle (identiques à la migration) pour éviter un rechargement. */
    protected $attributes = [
        'status' => 'active',
        'locale' => 'fr',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** L'uuid sert d'identifiant public ; la clé primaire reste un entier. */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(Role::values(Role::staff()));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isActive() && $this->isStaff();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getAppAuthenticationHolderName(): string
    {
        return $this->email ?? $this->name;
    }

    public function getActivitylogOptions(): LogOptions
    {
        // Journalise les changements de compte sans données sensibles.
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
