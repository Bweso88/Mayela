<?php

namespace App\Modules\Identity\Policies;

use App\Modules\Identity\Models\StudentProfile;
use App\Modules\Identity\Models\User;

class StudentProfilePolicy
{
    /** Un élève voit son profil ; un parent voit uniquement ses enfants rattachés. */
    public function view(User $user, StudentProfile $student): bool
    {
        if ($student->user_id === $user->id) {
            return true;
        }

        return (bool) $user->parentProfile?->isParentOf($student);
    }

    /** Seuls les parents rattachés modifient la scolarité de l'enfant. */
    public function update(User $user, StudentProfile $student): bool
    {
        return (bool) $user->parentProfile?->isParentOf($student);
    }
}
