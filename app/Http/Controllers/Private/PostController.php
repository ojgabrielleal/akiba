<?php

namespace App\Http\Controllers\Private;

use App\Services\PostService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\Post\PostResource;

use App\Models\Post;
use App\Integrations\AnimeNewsFeedService;

use Inertia\Inertia;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;

class PostController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Post';

    public function __construct(
        private PostService $postFilter,
        private AnimeNewsFeedService $newsFeed,
    ) {}

    private function indexPosts()
    {
        return $this->whenCanViewAny(Post::class,
            fn () => PostResource::collection(
                $this->postFilter->filter([
                'user' => request()->user(),
                    'active' => true,
                    'with_count' => [
                        'views',
                        'reviews as review_draft_count' => fn ($query) => $query->where('status', 'draft'),
                        'reviews as review_revision_count' => fn ($query) => $query->where('status', 'revision'),
                        'reviews as review_published_count' => fn ($query) => $query->where('status', 'published'),
                    ],
                    'with' => ['author', 'reviews'],
                    'search' => request()->input('search'),
                    'paginate' => 10,
                ])
            )->format('grid'),
        );
    }

    private function indexNewsFeedSources()
    {
        if (! request()->user()->hasPermission('post.feed.view')) {
            return null;
        }

        return $this->newsFeed->sources();
    }

    private function indexNewsFeedPosts()
    {
        if (! request()->user()->hasPermission('post.feed.view')) {
            return null;
        }

        return $this->newsFeed->paginate(
            request()->input('source'),
            6,
            (int) request()->input('feed_page', 1),
        );
    }

    public function showPost(Post $post)
    {
        $this->authorize('view', $post);
        $this->authorize('viewAny', Post::class);

        return Inertia::render($this->render, [
            'post' => $this->indexPost($post),
            'posts' => $this->indexEditablePosts(),
        ]);
    }

    public function storePost(StorePostRequest $request, PostService $service)
    {
        $service->store(
            $request->user(),
            $request->validated(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('save');
    }

    public function updatePost(UpdatePostRequest $request, PostService $service, Post $post)
    {
        $service->update(
            $post,
            $request->user(),
            $request->validated(),
            $request->file('image'),
            $request->file('cover')
        );

        return $this->flashMessage('update');
    }

    public function deactivatePost(PostService $service, Post $post)
    {
        $this->authorize('deactivate', $post);

        $service->deactivate($post);

        return $this->flashMessage('deactivate');
    }

    private function indexPost(Post $post): PostResource
    {
        return new PostResource($post->load(['tags', 'references', 'author', 'reviews.author']));
    }

    private function indexEditablePosts()
    {
        return PostResource::collection(
            $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'with_count' => [
                    'views',
                    'reviews as review_draft_count' => fn ($query) => $query->where('status', 'draft'),
                    'reviews as review_revision_count' => fn ($query) => $query->where('status', 'revision'),
                    'reviews as review_published_count' => fn ($query) => $query->where('status', 'published'),
                ],
                'with' => ['author', 'reviews.author'],
                'search' => request()->input('search'),
                'paginate' => 10,
            ])
        )->format('grid');
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
            'newsFeedSources' => $this->indexNewsFeedSources(),
            'selectedNewsFeedSource' => request()->input('source'),
            'newsFeedPosts' => $this->indexNewsFeedPosts(),
        ]);
    }
}
