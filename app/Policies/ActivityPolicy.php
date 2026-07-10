<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('activity.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activity.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('activity.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activity.update');
    }

    /**
     * Determine whether the user can delete the model (deactivate).
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activity.deactivate');
    }

    /**
     * Determine whether the user can participate in the activity.
     */
    public function participate(User $user, Activity $activity): bool
    {
        return $user->hasPermission('activity.participate');
    }
}
