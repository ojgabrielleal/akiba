<?php

namespace App\Policies;

use App\Models\Option;
use App\Models\User;

class OptionPolicy
{
    /**
     * Determine whether the user can vote on an option.
     */
    public function vote(User $user, Option $option): bool
    {
        return $user->hasPermission('poll.create.vote');
    }
}
