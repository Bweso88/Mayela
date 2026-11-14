<?php

namespace Database\Seeders;

use App\Modules\Identity\Enums\Permission as PermissionEnum;
use App\Modules\Identity\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Idempotent : peut être relancé à chaque déploiement. */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        foreach (PermissionEnum::matrix() as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions(
                array_map(fn (PermissionEnum $p) => $p->value, $permissions)
            );
        }

        // Garde-fou : chaque rôle déclaré dans l'enum existe.
        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }
    }
}
