<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Enums\Permission;
use App\Modules\Identity\Models\User;

/** Gestion des comptes depuis le back-office (permission users.manage). */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(Permission::ManageUsers->value) && ! $user->is($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permission::ManageUsers->value);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false; // Suppression définitive : uniquement via la procédure RGPD (phase 7).
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
