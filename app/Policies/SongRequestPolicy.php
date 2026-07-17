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
        return $user->hasPermission('song.request.list');
    }

    /**
     * Determine whether the user can mark the song request as played.
     */
    public function markAsPlayed(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermission('song.request.reproduce');
    }

    /**
     * Determine whether the user can mark the song request as canceled.
     */
    public function markAsCanceled(User $user, SongRequest $songRequest): bool
    {
        return $user->hasPermission('song.request.cancel');
    }

    /**
     * Determine whether the user can toggle the song request box status.
     */
    public function toggleBoxStatus(User $user): bool
    {
        return $user->hasPermission('song.request.toggle');
    }
}
