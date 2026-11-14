<?php

use App\Modules\Identity\Models\User;

function parentPayload(array $overrides = []): array
{
    return [
        'name' => 'Grâce Nkounkou',
        'email' => 'Grace@Example.com',
        'password' => 'motdepasse1',
        'password_confirmation' => 'motdepasse1',
        'device_name' => 'Pixel 7',
        'accept_terms' => true,
        ...$overrides,
    ];
}

it('inscrit un parent et renvoie un jeton', function () {
    $response = $this->postJson('/api/v1/auth/register-parent', parentPayload());

    $response->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'grace@example.com')
        ->assertJsonPath('user.roles', ['parent']);

    $user = User::firstWhere('email', 'grace@example.com');
    expect($user->parentProfile)->not->toBeNull()
        ->and($user->tokens()->first()->abilities)->toBe(['parent']);
});

it('accepte un numéro de téléphone à la place de l\'e-mail', function () {
    $this->postJson('/api/v1/auth/register-parent', parentPayload(['email' => null, 'phone' => '+242061234567']))
        ->assertCreated()
        ->assertJsonPath('user.phone', '+242061234567');
});

it('exige e-mail ou téléphone, un mot de passe robuste et l\'acceptation des conditions', function () {
    $this->postJson('/api/v1/auth/register-parent', parentPayload([
        'email' => null, 'password' => 'court', 'password_confirmation' => 'court', 'accept_terms' => false,
    ]))->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone', 'password', 'accept_terms']);
});

it('refuse un e-mail déjà utilisé', function () {
    User::factory()->create(['email' => 'grace@example.com']);

    $this->postJson('/api/v1/auth/register-parent', parentPayload())
        ->assertUnprocessable()->assertJsonValidationErrors('email');
});
