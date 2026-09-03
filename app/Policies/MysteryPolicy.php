<?php

namespace App\Policies;

use App\Models\Mystery;
use App\Models\User;

class MysteryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('mystery.list');
    }

    public function view(User $user, Mystery $mystery): bool
    {
        return $user->hasPermission('mystery.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('mystery.create');
    }

    public function update(User $user, Mystery $mystery): bool
    {
        return $user->hasPermission('mystery.update');
    }

    public function delete(User $user, Mystery $mystery): bool
    {
        return $user->hasPermission('mystery.delete');
    }

    public function publish(User $user, Mystery $mystery): bool
    {
        return $user->hasPermission('mystery.publish');
    }

    public function respond(User $user): bool
    {
        return $user->hasPermission('mystery.respond');
    }
}
