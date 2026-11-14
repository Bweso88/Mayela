<?php

namespace App\Modules\Identity\Support;

use App\Modules\Identity\Models\StudentProfile;

/**
 * Génère un code de connexion lisible par un enfant : préfixe + caractères
 * sans ambiguïté (pas de 0/O, 1/I/L). Exemple : MY-7KQ4PX.
 */
class StudentLoginCodeGenerator
{
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public function generate(): string
    {
        $prefix = config('mayela.student_login.code_prefix');
        $length = config('mayela.student_login.code_length');

        do {
            $code = $prefix.'-';
            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (StudentProfile::withTrashed()->where('login_code', $code)->exists());

        return $code;
    }

    public static function normalize(string $code): string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
        $prefix = config('mayela.student_login.code_prefix');

        return str_starts_with($code, $prefix)
            ? $prefix.'-'.substr($code, strlen($prefix))
            : $code;
    }
}
