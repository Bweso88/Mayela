<?php

namespace App\Modules\Identity\Http\Requests;

use Illuminate\Validation\Rules\Password;

/** Règles partagées par les formulaires d'identité. */
final class Concerns
{
    public const PHONE_REGEX = '/^\+?[0-9]{8,15}$/';

    public static function password(): array
    {
        return ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()];
    }

    public static function deviceName(): array
    {
        return ['required', 'string', 'max:100'];
    }

    public static function birthDate(): array
    {
        return [
            'required', 'date_format:Y-m-d',
            'before_or_equal:'.now()->subYears(config('mayela.student_min_age'))->toDateString(),
            'after_or_equal:'.now()->subYears(config('mayela.student_max_age'))->toDateString(),
        ];
    }

    public static function countryCode(): array
    {
        return ['required', 'string', 'size:2', 'exists:countries,code'];
    }

    public static function normalizeEmail(?string $email): ?string
    {
        return $email === null ? null : mb_strtolower(trim($email));
    }
}
