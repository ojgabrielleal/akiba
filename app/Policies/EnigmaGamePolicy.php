<?php

namespace App\Policies;

use App\Models\EnigmaGame;
use App\Models\User;

class EnigmaGamePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('enigmagame.list');
    }

    public function view(User $user, EnigmaGame $enigmagame): bool
    {
        return $user->hasPermission('enigmagame.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('enigmagame.create');
    }

    public function update(User $user, EnigmaGame $enigmagame): bool
    {
        return $user->hasPermission('enigmagame.update');
    }

    public function delete(User $user, EnigmaGame $enigmagame): bool
    {
        return $user->hasPermission('enigmagame.delete');
    }

    public function publish(User $user, EnigmaGame $enigmagame): bool
    {
        return $user->hasPermission('enigmagame.publish');
    }

    public function respond(User $user): bool
    {
        return $user->hasPermission('enigmagame.respond');
    }
}
