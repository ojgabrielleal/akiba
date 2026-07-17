<?php

namespace App\Policies;

use App\Models\Repository;
use App\Models\User;

class RepositoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('repository.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Repository $repository): bool
    {
        return $user->hasPermission('repository.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('repository.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Repository $repository): bool
    {
        return $user->hasPermission('repository.update');
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, Repository $repository): bool
    {
        return $user->hasPermission('repository.deactivate');
    }
}
