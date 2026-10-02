<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;

class BadgePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('badge.list');
    }

    public function view(User $user, Badge $badge): bool
    {
        return $user->hasPermission('badge.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('badge.create');
    }

    public function update(User $user, Badge $badge): bool
    {
        return $user->hasPermission('badge.update');
    }

    public function delete(User $user, Badge $badge): bool
    {
        return $user->hasPermission('badge.delete');
    }
}
