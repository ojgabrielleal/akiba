<?php

namespace App\Actions\User;

use App\Models\Role;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class StoreUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->storeUser($data);
            $this->attachRoles($user, $data['roles'] ?? []);
            $this->storeDefaults($user);

            return $user;
        });
    }

    private function storeUser(array $data): User
    {
        return User::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'name' => $data['name'],
            'avatar' => "/img/placeholders/avatar-{$data['gender']}.webp",
            'nickname' => $data['nickname'],
            'gender' => $data['gender'],
            'is_virtual' => $data['is_virtual'],
        ]);
    }

    private function attachRoles(User $user, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)
            ->pluck('id')
            ->toArray();

        $user->roles()->attach($roles);
    }

    private function storeDefaults(User $user): void
    {
        $user->socials()->createMany($this->defaultSocials());
        $user->preferences()->createMany($this->defaultPreferences());
        $user->favorites()->createMany($this->defaultFavorites());
    }

    private function defaultSocials(): array
    {
        return [
            ['name' => 'Twitter', 'url' => null],
            ['name' => 'Facebook', 'url' => null],
            ['name' => 'Instagram', 'url' => null],
            ['name' => 'Youtube', 'url' => null],
            ['name' => 'Discord', 'url' => null],
        ];
    }

    private function defaultPreferences(): array
    {
        return [
            ['is_like' => true, 'content' => null],
            ['is_like' => true, 'content' => null],
            ['is_like' => true, 'content' => null],
            ['is_like' => false, 'content' => null],
            ['is_like' => false, 'content' => null],
            ['is_like' => false, 'content' => null],
        ];
    }

    private function defaultFavorites(): array
    {
        return [
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
        ];
    }
}
