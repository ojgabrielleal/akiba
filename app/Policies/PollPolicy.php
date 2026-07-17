<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

class PollPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('poll.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Poll $poll): bool
    {
        return $user->hasPermission('poll.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('poll.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Poll $poll): bool
    {
        return $user->hasPermission('poll.update');
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, Poll $poll): bool
    {
        return $user->hasPermission('poll.deactivate');
    }
}
