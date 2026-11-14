<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterParent
{
    /**
     * @param  array{name: string, email?: ?string, phone?: ?string, password: string}  $data
     */
    public function __invoke(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);

            $user->assignRole(Role::Parent->value);
            $user->parentProfile()->create();

            return $user->load('parentProfile');
        });
    }
}
