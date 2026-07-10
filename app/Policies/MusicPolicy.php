<?php

namespace App\Policies;

use App\Models\Music;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MusicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('music.list');
    }

    public function update(User $user, Music $music): bool
    {
        return $user->hasPermission('music.update');
    }

    /**
     * Determine whether the user can set the ranking.
     */
    public function setRanking(User $user): bool
    {
        return $user->hasPermission('music.set.ranking');
    }
}
