<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('task.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->hasPermission('task.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('task.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasPermission('task.update');
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, Task $task): bool
    {
        return $user->hasPermission('task.deactivate');
    }

    /**
     * Determine whether the user can mark the task for review.
     */
    public function markForReview(User $user, Task $task): bool
    {
        return $user->hasPermission('task.review');
    }

    /**
     * Determine whether the user can complete the task review.
     */
    public function complete(User $user, Task $task): bool
    {
        return $user->hasPermission('task.review');
    }
}
