<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Models\StudentProfile;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Support\StudentLoginCodeGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Connexion enfant : code élève + PIN (défini par le parent). */
class AuthenticateStudentWithCode
{
    public function __invoke(string $code, string $pin): User
    {
        $student = StudentProfile::with('user')
            ->where('login_code', StudentLoginCodeGenerator::normalize($code))
            ->first();

        if (! $student || ! $student->pin_hash || ! Hash::check($pin, $student->pin_hash)) {
            throw ValidationException::withMessages(['login_code' => __('Code ou PIN incorrect.')]);
        }

        if (! $student->user->isActive()) {
            throw ValidationException::withMessages(['login_code' => __('Ce compte est suspendu.')]);
        }

        return $student->user;
    }
}
