<?php

use App\Modules\Identity\Enums\Permission;
use App\Modules\Identity\Enums\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role as RoleModel;

it('crée les six rôles et reste idempotent', function () {
    $this->seed(RolesAndPermissionsSeeder::class); // second passage

    expect(RoleModel::pluck('name')->sort()->values()->all())
        ->toBe(collect(Role::cases())->pluck('value')->sort()->values()->all());
});

it('donne toutes les permissions au super-admin et uniquement l\'édition au contributeur', function () {
    expect(RoleModel::findByName('super_admin')->permissions)->toHaveCount(count(Permission::cases()))
        ->and(RoleModel::findByName('contributor')->permissions->pluck('name')->all())->toBe(['content.edit']);
});
