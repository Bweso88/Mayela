<?php

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\User;

it('connecte un parent par e-mail', function () {
    User::factory()->withRole(Role::Parent)->create(['email' => 'p@example.com']);

    $this->postJson('/api/v1/auth/login', ['identifier' => 'p@example.com', 'password' => 'password', 'device_name' => 'iPhone'])
        ->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'roles', 'children']]);
});

it('connecte un parent par téléphone', function () {
    User::factory()->withRole(Role::Parent)->create(['phone' => '+242055555555']);

    $this->postJson('/api/v1/auth/login', ['identifier' => '+242055555555', 'password' => 'password', 'device_name' => 'Tecno'])
        ->assertOk();
});

it('refuse un mauvais mot de passe', function () {
    User::factory()->withRole(Role::Parent)->create(['email' => 'p@example.com']);

    $this->postJson('/api/v1/auth/login', ['identifier' => 'p@example.com', 'password' => 'faux', 'device_name' => 'x'])
        ->assertUnprocessable()->assertJsonValidationErrors('identifier');
});

it('n\'ouvre pas l\'API mobile au personnel du back-office', function () {
    User::factory()->withRole(Role::SuperAdmin)->create(['email' => 'admin@example.com']);

    $this->postJson('/api/v1/auth/login', ['identifier' => 'admin@example.com', 'password' => 'password', 'device_name' => 'x'])
        ->assertUnprocessable();
});

it('refuse la connexion d\'un compte suspendu', function () {
    User::factory()->withRole(Role::Parent)->suspended()->create(['email' => 'p@example.com']);

    $this->postJson('/api/v1/auth/login', ['identifier' => 'p@example.com', 'password' => 'password', 'device_name' => 'x'])
        ->assertUnprocessable();
});

it('limite les tentatives de connexion', function () {
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v1/auth/login', ['identifier' => 'x@example.com', 'password' => 'faux', 'device_name' => 'x']);
    }

    $this->postJson('/api/v1/auth/login', ['identifier' => 'x@example.com', 'password' => 'faux', 'device_name' => 'x'])
        ->assertTooManyRequests();
});

it('déconnecte en révoquant le jeton de l\'appareil', function () {
    $user = User::factory()->withRole(Role::Parent)->create();
    $token = $user->createToken('phone', ['parent'])->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

it('bloque un compte suspendu même avec un jeton valide', function () {
    $user = User::factory()->withRole(Role::Parent)->create();
    $token = $user->createToken('phone', ['parent'])->plainTextToken;
    $user->update(['status' => 'suspended']);

    $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
});
