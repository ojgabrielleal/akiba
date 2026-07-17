<?php

namespace App\Policies;

use App\Models\User;

class ListenerMonthPolicy
{
    /**
     * Determine whether the user can view the listener of the month.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('listener.month.view');
    }

    /**
     * Determine whether the user can set the listener of the month.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('listener.month.set');
    }
}
