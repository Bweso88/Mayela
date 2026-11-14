<?php

namespace App\Modules\Curriculum\Policies;

use App\Modules\Curriculum\Enums\PublicationStatus;
use App\Modules\Curriculum\Models\Chapter;
use App\Modules\Identity\Enums\Permission;
use App\Modules\Identity\Models\User;

/** Chapitres : rédaction avec content.edit ; un chapitre publié ne se modifie qu'avec content.publish. */
class ChapterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::EditContent->value) || $user->can(Permission::ManageCurriculum->value);
    }

    public function view(User $user, Chapter $chapter): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::EditContent->value);
    }

    public function update(User $user, Chapter $chapter): bool
    {
        if ($chapter->status === PublicationStatus::Published) {
            return $user->can(Permission::PublishContent->value);
        }

        return $user->can(Permission::EditContent->value);
    }

    public function delete(User $user, Chapter $chapter): bool
    {
        return $user->can(Permission::PublishContent->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permission::PublishContent->value);
    }

    public function restore(User $user, Chapter $chapter): bool
    {
        return $user->can(Permission::PublishContent->value);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permission::PublishContent->value);
    }

    public function forceDelete(User $user, Chapter $chapter): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
