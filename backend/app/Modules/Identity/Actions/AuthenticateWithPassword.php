<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Connexion mobile par e-mail ou téléphone + mot de passe (parents, élèves autonomes).
 * Le personnel utilise le back-office web (avec double authentification), pas l'API.
 */
class AuthenticateWithPassword
{
    public function __invoke(string $identifier, string $password): User
    {
        $field = str_contains($identifier, '@') ? 'email' : 'phone';
        $user = User::where($field, $field === 'email' ? mb_strtolower($identifier) : $identifier)->first();

        if (! $user || ! Hash::check($password, $user->password)
            || ! $user->hasAnyRole(Role::values(Role::appUsers()))) {
            throw ValidationException::withMessages(['identifier' => __('auth.failed')]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages(['identifier' => __('Ce compte est suspendu.')]);
        }

        return $user;
    }
}
