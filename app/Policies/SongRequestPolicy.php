<?php

namespace App\Policies;

use App\Models\SongRequest;
use App\Models\User;

class SongRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('songrequest.list');
    }

    /**
     * Determine whether the user can reproduce models.
     */
    public function reproduce(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermission('songrequest.reproduce');
    }

    /**
     * Determine whether the user can cancel models.
     */
    public function cancel(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermission('songrequest.cancel');
    }

    /**
     * Determine whether the user can toggle models.
     */
    public function toggle(User $user): bool
    {
        return $user->hasPermission('songrequest.toggle');
    }
}
