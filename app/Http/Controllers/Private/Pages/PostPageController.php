<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\PostFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\Post\PostResource;

use App\Models\Post;

use Inertia\Inertia;

class PostPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Post';

    public function __construct(
        private PostFilter $postFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->whenCanViewAny(Post::class,
                fn () => PostResource::collection(
                    $this->postFilter->apply(request()->user(), [
                        'active' => true,
                        'with_count' => 'views',
                        'with' => ['author', 'reviews.author'],
                        'search' => request()->input('search'),
                        'paginate' => 10,
                    ])
                ),
            ),
        ]);
    }
}
