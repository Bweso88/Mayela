<?php

namespace App\Modules\Curriculum\Policies;

use App\Modules\Identity\Enums\Permission;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Structure et programme (ordres, classes, séries, années, programmes, matières) :
 * lecture pour l'équipe pédagogique, modification avec curriculum.manage.
 */
class CurriculumStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ManageCurriculum->value) || $user->can(Permission::EditContent->value);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ManageCurriculum->value);
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can(Permission::ManageCurriculum->value);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can(Permission::ManageCurriculum->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::ManageCurriculum->value);
    }

    public function reorder(User $user): bool
    {
        return $user->can(Permission::ManageCurriculum->value);
    }
}
