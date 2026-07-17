<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->hasPermission('user.view')) {
            return true;
        }

        return $user->hasPermission('user.view.own') && $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->hasPermission('user.update')) {
            return true;
        }

        return $user->hasPermission('user.update.own') && $user->id === $model->id;
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, User $model): bool
    {
        return $user->hasPermission('user.deactivate');
    }

    /**
     * Determine whether the user can update the model access.
     */
    public function updateAccess(User $user, User $model): bool
    {
        return $user->hasPermission('user.authority.update');
    }
}
