<?php

namespace App\Actions\Post;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;

class ListPostsAction
{
    public function execute(User $user)
    {
        $query = Post::active()
            ->with(['author', 'views', 'postReviews.author'])
            ->orderBy('created_at', 'desc');

        if ($user->hasPermission('post.list')) {
            return PostResource::collection($query->paginate(10))->format('summary');
        }

        if ($user->hasPermission('post.list.own')) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('postReviews', fn ($query) => $query->where('user_id', $user->id));
            });
        }

        return PostResource::collection($query->paginate(10))->format('summary');
    }
}
