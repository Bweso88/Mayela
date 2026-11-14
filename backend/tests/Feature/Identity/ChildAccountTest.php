<?php

use App\Modules\Identity\Enums\ConsentType;
use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\StudentProfile;
use Laravel\Sanctum\Sanctum;

function childPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Lucas',
        'last_name' => 'Mabiala',
        'birth_date' => now()->subYears(8)->toDateString(),
        'country_code' => 'CG',
        'pin' => '2468',
        'pin_confirmation' => '2468',
        'parental_consent' => true,
        ...$overrides,
    ];
}

it('permet à un parent de créer le compte de son enfant avec consentement horodaté', function () {
    $parent = actingAsParent();

    $response = $this->postJson('/api/v1/parent/children', childPayload())
        ->assertCreated()
        ->assertJsonPath('data.first_name', 'Lucas')
        ->assertJsonPath('data.country_code', 'CG')
        ->assertJsonPath('data.requires_parental_consent', true);

    expect($response->json('data.login_code'))->toMatch('/^MY-[2-9A-Z]{6}$/');

    $student = StudentProfile::firstWhere('uuid', $response->json('data.id'));
    expect($student->user->hasRole(Role::Student->value))->toBeTrue()
        ->and($student->user->email)->toBeNull()
        ->and($parent->parentProfile->isParentOf($student))->toBeTrue()
        ->and($student->consents()->first())
        ->type->toBe(ConsentType::Account)
        ->parent_profile_id->toBe($parent->parentProfile->id);
});

it('exige le consentement parental et un PIN confirmé', function () {
    actingAsParent();

    $this->postJson('/api/v1/parent/children', childPayload(['parental_consent' => false, 'pin_confirmation' => '0000']))
        ->assertUnprocessable()->assertJsonValidationErrors(['parental_consent', 'pin']);
});

it('liste uniquement les enfants du parent connecté', function () {
    $parent = actingAsParent();
    childOf($parent, ['first_name' => 'Awa']);
    childOf(parentUser(), ['first_name' => 'Autre']);

    $this->getJson('/api/v1/parent/children')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.first_name', 'Awa');
});

it('interdit de consulter l\'enfant d\'un autre parent', function () {
    $other = childOf(parentUser());
    actingAsParent();

    $this->getJson("/api/v1/parent/children/{$other->uuid}")->assertForbidden();
});

it('interdit l\'espace parent à un jeton élève', function () {
    $student = childOf(parentUser());
    Sanctum::actingAs($student->user, [Role::Student->value]);

    $this->getJson('/api/v1/parent/children')->assertForbidden();
});

it('connecte un enfant avec son code et son PIN', function () {
    $student = childOf(parentUser());

    $this->postJson('/api/v1/auth/student-login', [
        'login_code' => strtolower(str_replace('-', '', $student->login_code)), // saisie tolérante
        'pin' => '2468',
        'device_name' => 'Tablette',
    ])->assertOk()->assertJsonPath('user.student_profile.id', $student->uuid);

    expect($student->user->tokens()->first()->abilities)->toBe(['student']);
});

it('refuse un mauvais PIN puis limite les essais', function () {
    $student = childOf(parentUser());
    $attempt = fn () => $this->postJson('/api/v1/auth/student-login', [
        'login_code' => $student->login_code, 'pin' => '0000', 'device_name' => 'x',
    ]);

    $attempt()->assertUnprocessable()->assertJsonValidationErrors('login_code');
    foreach (range(1, 4) as $_) {
        $attempt();
    }
    $attempt()->assertTooManyRequests();
});

it('renvoie le profil élève sur /me', function () {
    $student = childOf(parentUser());
    Sanctum::actingAs($student->user, [Role::Student->value]);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.student_profile.first_name', 'Lucas')
        ->assertJsonMissingPath('data.student_profile.pin_hash');
});
