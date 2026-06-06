<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

trait ResolvesUserLogged
{
    public function getUserLogged()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $user->load('roles.permissions');

        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'slug' => $user->slug,
            'name' => $user->name,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'roles' => $user->roles,
            'permissions' => $user->roles
                ->flatMap(fn ($role) => $role->permissions)
                ->pluck('name')
                ->unique()
                ->values(),
        ];
    }
}
