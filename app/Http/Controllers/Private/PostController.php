<?php

namespace App\Http\Controllers\Private;

use App\Actions\Post\StorePostAction;
use App\Actions\Post\UpdatePostAction;

use App\Filters\PostFilter;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;

use App\Http\Resources\Post\PostResource;

use App\Models\Post;

use Inertia\Inertia;

class PostController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Post';

    public function __construct(
        private PostFilter $postFilter,
        private StorePostAction $storePostAction,
        private UpdatePostAction $updatePostAction,
    ) {}

    public function show(Post $post)
    {
        $this->authorize('view', $post);
        $this->authorize('viewAny', Post::class);

        return Inertia::render($this->render, [
            'post' => new PostResource($post->load(['tags', 'references', 'author', 'reviews.author'])),
            'posts' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'with_count' => 'views',
                    'with' => ['author', 'reviews.author'],
                    'search' => request()->input('search'),
                    'paginate' => 10,
                ])
            ),
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $this->storePostAction->execute(
            $request->user(),
            $request->all(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('save');
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->updatePostAction->execute(
            $post,
            $request->all(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('update');
    }
}
