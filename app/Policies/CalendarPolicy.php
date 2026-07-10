<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('calendar.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Calendar $calendar): bool
    {
        return $user->hasPermission('calendar.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('calendar.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Calendar $calendar): bool
    {
        return $user->hasPermission('calendar.update');
    }

    /**
     * Determine whether the user can delete the model (deactivate).
     */
    public function delete(User $user, Calendar $calendar): bool
    {
        return $user->hasPermission('calendar.deactivate');
    }
}
