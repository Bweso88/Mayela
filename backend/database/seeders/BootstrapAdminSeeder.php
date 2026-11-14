<?php

namespace Database\Seeders;

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crée le premier super-admin à partir de MAYELA_ADMIN_EMAIL / MAYELA_ADMIN_PASSWORD.
 * Ne fait rien si ces variables sont absentes (aucun mot de passe par défaut codé en dur).
 */
class BootstrapAdminSeeder extends Seeder
{
    public function run(): void
    {
        ['email' => $email, 'password' => $password] = config('mayela.bootstrap_admin');

        if (! $email || ! $password) {
            $this->command?->warn('MAYELA_ADMIN_EMAIL / MAYELA_ADMIN_PASSWORD non définis : aucun admin créé.');

            return;
        }

        $user = User::firstOrCreate(
            ['email' => mb_strtolower($email)],
            ['name' => 'Administrateur Mayela', 'password' => $password],
        );
        $user->assignRole(Role::SuperAdmin->value);
    }
}
