<?php

namespace App\Actions\Administration\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roles = Role::whereIn('name', $data['roles'] ?? [])
                ->pluck('id')
                ->toArray();

            $avatar = ($data['gender']) === 'male' 
                ? '/img/defaults/user-male.webp' 
                : '/img/defaults/user-female.webp';

            $user = User::create([
                'username' => $data['username'],
                'password' => $data['password'],
                'name' => $data['name'],
                'avatar' => $avatar,
                'nickname' => $data['nickname'],
                'gender' => $data['gender'],
                'is_virtual' => $data['is_virtual'],
            ]);

            $socials = [
                ['name' => 'Twitter', 'url' => null],
                ['name' => 'Facebook', 'url' => null],
                ['name' => 'Instagram', 'url' => null],
                ['name' => 'Youtube', 'url' => null],
                ['name' => 'Discord', 'url' => null],
            ];

            $preferences = [
                ['is_like' => true, 'content' => null],
                ['is_like' => true, 'content' => null],
                ['is_like' => true, 'content' => null],
                ['is_like' => false, 'content' => null],
                ['is_like' => false, 'content' => null],
                ['is_like' => false, 'content' => null],
            ];

            $favorites = [
                ['name' => null, 'image' => null],
                ['name' => null, 'image' => null],
                ['name' => null, 'image' => null],
            ];

            $user->roles()->attach($roles);
            $user->socials()->createMany($socials);
            $user->preferences()->createMany($preferences);
            $user->favorites()->createMany($favorites);

            return $user;
        });
    }
}
