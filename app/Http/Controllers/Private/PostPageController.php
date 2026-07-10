<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\ResolvesUserLogged;

use App\Models\Post;

use App\Http\Resources\PostResource;

class PostPageController extends Controller
{
    use ResolvesUserLogged;

    private $render = 'private/Post';

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
        ]);
    }

    public function indexPosts()
    {
        $user = request()->user();
        
        $query = Post::active()
            ->with(['author', 'views', 'event', 'review.opinions'])
            ->orderBy('created_at','desc');

        if ($user->hasPermission('post.list')) {
            return PostResource::collection($query->paginate(10))->format('summary');
        }

        if ($user->hasPermission('post.list.own')) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('review');
            });
        }

        return PostResource::collection($query->paginate(10))->format('summary');
    }

}
