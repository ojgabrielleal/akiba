<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    public function approve(User $user, Comment $comment): bool
    {
        return false;
    }

    public function hide(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comment.hide');
    }

    public function restore(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comment.restore');
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->hasPermission('comment.delete');
    }
}
