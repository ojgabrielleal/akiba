<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can list results of the model.
     */
    public function list(User $user): bool
    {
        return $user->hasPermission('post.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->hasPermission('post.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('post.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->hasPermission('post.update');
    }

    /**
     * Determine whether the user can delete the model (deactivate).
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->hasPermission('post.deactivate');
    }

    /**
     * Determine whether the user can approve a post in revision.
     */
    public function approve(User $user, Post $post): bool
    {
        return $user->hasPermission('post.approve');
    }
}
