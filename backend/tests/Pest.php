<?php

use App\Modules\Identity\Actions\CreateStudentProfile;
use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\StudentProfile;
use App\Modules\Identity\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed([RolesAndPermissionsSeeder::class, CountrySeeder::class]);
    })
    ->in('Feature', 'Unit');

/*
| Helpers partagés
*/

function parentUser(): User
{
    return User::factory()->withRole(Role::Parent)->create();
}

function actingAsParent(?User $parent = null): User
{
    $parent ??= parentUser();
    Sanctum::actingAs($parent, [Role::Parent->value]);

    return $parent;
}

/** Crée un enfant rattaché au parent, avec le PIN 2468. */
function childOf(User $parent, array $overrides = []): StudentProfile
{
    return app(CreateStudentProfile::class)([
        'first_name' => 'Lucas',
        'last_name' => 'Mabiala',
        'birth_date' => now()->subYears(8)->toDateString(),
        'country_code' => 'CG',
        'pin' => '2468',
        ...$overrides,
    ], $parent->parentProfile);
}
