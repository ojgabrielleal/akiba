<?php

namespace App\Actions\Administration\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserAccessAction
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if ($data['roles']) {
                $roles = Role::whereIn('name', $data['roles'])
                    ->pluck('id')
                    ->toArray();

                $user->roles()->sync($roles);
            }

            if (!empty($data['password'])) {
                $user->update([
                    'password' => $data['password']
                ]);
            }

            return $user;
        });
    }
}
