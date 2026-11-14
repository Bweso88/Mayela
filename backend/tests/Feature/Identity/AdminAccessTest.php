<?php

use App\Filament\Resources\Users\UserResource;
use App\Modules\Identity\Enums\Permission;
use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Policies\UserPolicy;
use Filament\Facades\Filament;

it('réserve le back-office au personnel actif', function (Role $role, bool $allowed) {
    $user = User::factory()->withRole($role)->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBe($allowed);
})->with([
    'super admin' => [Role::SuperAdmin, true],
    'admin pédagogique' => [Role::PedagogicalAdmin, true],
    'contributeur' => [Role::Contributor, true],
    'modérateur' => [Role::Moderator, true],
    'parent' => [Role::Parent, false],
    'élève' => [Role::Student, false],
]);

it('refuse le back-office à un membre du personnel suspendu', function () {
    $user = User::factory()->withRole(Role::SuperAdmin)->suspended()->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('redirige un parent hors du back-office', function () {
    $this->actingAs(User::factory()->withRole(Role::Parent)->create())
        ->get('/admin')
        ->assertForbidden();
});

it('n\'autorise la gestion des utilisateurs qu\'avec la permission dédiée', function () {
    $superAdmin = User::factory()->withRole(Role::SuperAdmin)->create();
    $pedagogue = User::factory()->withRole(Role::PedagogicalAdmin)->create();

    $this->actingAs($superAdmin);
    expect(UserResource::canViewAny())->toBeTrue();

    $this->actingAs($pedagogue);
    expect(UserResource::canViewAny())->toBeFalse()
        ->and($pedagogue->can(Permission::PublishContent->value))->toBeTrue();
});

it('empêche un admin de supprimer son propre compte', function () {
    $admin = User::factory()->withRole(Role::SuperAdmin)->create();
    $policy = new UserPolicy;

    // Le Gate::before du super-admin ne s'applique pas à l'appel direct de la policy.
    expect($policy->delete($admin, $admin))->toBeFalse();
});
