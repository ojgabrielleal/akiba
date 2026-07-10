<?php

namespace App\Http\Controllers\Private\Post;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Inertia\Inertia;

class ShowPostController extends Controller
{
    private $render = 'private/Post';

    public function __invoke(Post $post)
    {
        $this->authorize('view', $post);

        return Inertia::render($this->render, [
            'post' => new PostResource($post->load(['tags', 'references', 'author', 'review.opinions'])),
            'posts' => $this->indexPosts(),
        ]);
    }

    private function indexPosts()
    {
        $user = request()->user();

        $query = Post::active()
            ->with(['author', 'views', 'event', 'review.opinions'])
            ->orderBy('created_at', 'desc');

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
