<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['post.list', 'post.list.own']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->hasPermission('post.view')
            || ($post->user_id === $user->id && $user->hasPermission('post.list.own'));
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
        return $user->hasPermission('post.update')
            || ($post->user_id === $user->id && $user->hasPermission('post.list.own'));
    }

    /**
     * Determine whether the user can deactivate the model.
     */
    public function deactivate(User $user, Post $post): bool
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
