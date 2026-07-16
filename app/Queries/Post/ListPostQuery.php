<?php

namespace App\Queries\Post;

use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Models\User;

class ListPostQuery
{
    public function handle(User $user)
    {
        if ($user->cannot('viewAny', Post::class)) {
            abort(403);
        }
        
        $query = Post::active()
            ->with(['author', 'views', 'reviews.author'])
            ->orderBy('created_at', 'desc');

        if ($user->hasPermission('post.list')) {
            return PostResource::collection($query->paginate(10))->format('summary');
        }

        if ($user->hasPermission('post.list.own')) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('reviews', fn ($query) => $query->where('user_id', $user->id));
            });
        }

        return PostResource::collection($query->paginate(10))->format('summary');
    }
}
