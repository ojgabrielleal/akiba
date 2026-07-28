<?php

namespace App\Actions\User;

use App\Models\Role;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class UpdateUserAccessAction
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $this->syncRoles($user, $data);
            $this->updatePassword($user, $data);

            return $user;
        });
    }

    private function syncRoles(User $user, array $data): void
    {
        if (! array_key_exists('roles', $data)) {
            return;
        }

        $roles = Role::whereIn('name', $data['roles'] ?? [])
            ->pluck('id')
            ->toArray();

        $user->roles()->sync($roles);
    }

    private function updatePassword(User $user, array $data): void
    {
        if (empty($data['password'])) {
            return;
        }

        $user->update([
            'password' => $data['password'],
        ]);
    }
}
