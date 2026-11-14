<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Models\User;

/** Crée un jeton Sanctum par appareil, avec la capacité correspondant au rôle. */
class IssueApiToken
{
    public function __invoke(User $user, string $deviceName, string $ability): string
    {
        $ttl = config('mayela.token_ttl_minutes');

        $user->forceFill(['last_login_at' => now()])->save();

        return $user->createToken(
            name: mb_substr($deviceName, 0, 100),
            abilities: [$ability],
            expiresAt: $ttl ? now()->addMinutes($ttl) : null,
        )->plainTextToken;
    }
}
