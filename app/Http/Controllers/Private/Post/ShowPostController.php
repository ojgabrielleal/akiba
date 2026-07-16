<?php

namespace App\Http\Controllers\Private\Post;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Resources\Post\PostResource;
use App\Queries\Post\ListPostQuery;
use App\Models\Post;

class ShowPostController extends Controller
{
    private $render = 'private/Post';

    public function __invoke(Post $post, ListPostQuery $listPostQuery)
    {
        $this->authorize('view', $post);

        return Inertia::render($this->render, [
            'post' => new PostResource($post->load(['tags', 'references', 'author', 'reviews.author'])),
            'posts' => $listPostQuery->handle(request()->user()),
        ]);
    }
}
