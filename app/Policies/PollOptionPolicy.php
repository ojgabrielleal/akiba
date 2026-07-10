<?php

namespace App\Policies;

use App\Models\PollOption;
use App\Models\User;

class PollOptionPolicy
{
    /**
     * Determine whether the user can vote on an option.
     */
    public function vote(User $user, PollOption $option): bool
    {
        return $user->hasPermission('poll.create.vote');
    }
}
