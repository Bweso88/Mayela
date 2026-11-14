<?php

function studentPayload(int $age): array
{
    return [
        'first_name' => 'Sarah',
        'last_name' => 'Okemba',
        'birth_date' => now()->subYears($age)->subDay()->toDateString(),
        'country_code' => 'CG',
        'email' => 'sarah@example.com',
        'password' => 'motdepasse1',
        'password_confirmation' => 'motdepasse1',
        'device_name' => 'Samsung A15',
        'accept_terms' => true,
    ];
}

it('refuse l\'inscription autonome avant 15 ans', function () {
    $this->postJson('/api/v1/auth/register-student', studentPayload(14))
        ->assertUnprocessable()->assertJsonValidationErrors('birth_date');

    $this->assertDatabaseMissing('users', ['email' => 'sarah@example.com']);
});

it('accepte l\'inscription autonome à partir de 15 ans', function () {
    $this->postJson('/api/v1/auth/register-student', studentPayload(15))
        ->assertCreated()
        ->assertJsonPath('user.roles', ['student'])
        ->assertJsonPath('user.student_profile.requires_parental_consent', false);

    $this->assertDatabaseHas('consents', ['type' => 'account', 'parent_profile_id' => null]);
});

it('refuse un pays inconnu', function () {
    $this->postJson('/api/v1/auth/register-student', [...studentPayload(16), 'country_code' => 'ZZ'])
        ->assertUnprocessable()->assertJsonValidationErrors('country_code');
});
