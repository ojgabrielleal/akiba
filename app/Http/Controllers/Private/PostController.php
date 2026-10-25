<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Requests\Post\StorePostRequest;

use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;

use App\Integrations\AnimeNewsFeedService;
use App\Models\Post;
use App\Services\PostService;
use Inertia\Inertia;

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
                        'comments',
                        'reviews as review_draft_count' => fn ($query) => $query->where('status', 'draft'),
                        'reviews as review_revision_count' => fn ($query) => $query->where('status', 'revision'),
                        'reviews as review_published_count' => fn ($query) => $query->where('status', 'published'),
                    ],
                    'with' => [
                        'author',
                        'reviews' => fn ($query) => $query
                            ->where('user_id', request()->user()->id)
                            ->select(['id', 'post_id', 'user_id', 'status']),
                    ],
                    'search' => mb_substr(trim(request()->string('search')->toString()), 0, 200),
                    'module' => in_array(request()->input('post_type'), ['post', 'review', 'event'], true)
                        ? request()->input('post_type') : null,
                    'status' => in_array(request()->input('post_status'), ['draft', 'revision', 'published'], true)
                        ? request()->input('post_status') : null,
                    'created_on' => request()->string('post_date')->toString(),
                    'order_by' => request()->input('post_sort') === 'views' ? 'views_count' : 'id',
                    'order_direction' => request()->input('post_sort') === 'oldest' ? 'asc' : 'desc',
                    'paginate' => 10,
                ])
            )->format('grid'),
        );
    }

    private function canViewNewsFeed(): bool
    {
        return request()->user()->hasPermission('post.feed.view');
    }

    private function indexNewsFeedSources()
    {
        if (! $this->canViewNewsFeed()) {
            return null;
        }

        return $this->newsFeed->sources();
    }

    private function indexNewsFeedPosts()
    {
        if (! $this->canViewNewsFeed()) {
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
            'postCapabilities' => $this->postCapabilities($post),
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

    public function destroyPost(PostService $service, Post $post)
    {
        $this->authorize('delete', $post);

        $service->deactivate($post);

        return $this->flashMessage('delete', 'Rascunho removido da lista. Ele ainda pode ser recuperado pela lixeira.');
    }

    private function indexPost(Post $post): PostResource
    {
        return PostResource::make(
            $post->load([
                'tags',
                'references',
                'author',
                'reviews.author',
                'comments' => fn ($query) => $query->with('author')->latest()->limit(20),
            ])->loadCount('comments')
        )->format('detail');
    }

    private function indexEditablePosts()
    {
        return PostResource::collection(
            $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'with_count' => [
                    'views',
                    'comments',
                    'reviews as review_draft_count' => fn ($query) => $query->where('status', 'draft'),
                    'reviews as review_revision_count' => fn ($query) => $query->where('status', 'revision'),
                    'reviews as review_published_count' => fn ($query) => $query->where('status', 'published'),
                ],
                'with' => [
                    'author',
                    'reviews' => fn ($query) => $query
                        ->where('user_id', request()->user()->id)
                        ->select(['id', 'post_id', 'user_id', 'status']),
                ],
                'search' => mb_substr(trim(request()->string('search')->toString()), 0, 200),
                'module' => in_array(request()->input('post_type'), ['post', 'review', 'event'], true)
                    ? request()->input('post_type') : null,
                'status' => in_array(request()->input('post_status'), ['draft', 'revision', 'published'], true)
                    ? request()->input('post_status') : null,
                'created_on' => request()->string('post_date')->toString(),
                'order_by' => request()->input('post_sort') === 'views' ? 'views_count' : 'id',
                'order_direction' => request()->input('post_sort') === 'oldest' ? 'asc' : 'desc',
                'paginate' => 10,
            ])
        )->format('grid');
    }

    private function postCapabilities(?Post $post = null): array
    {
        $user = request()->user();

        return [
            'submitForReview' => $post
                ? $user->can('update', $post)
                : $user->can('create', Post::class),
            'publish' => $user->hasPermission('post.publish'),
            'approve' => $post?->status === 'revision' && $user->can('approve', $post),
        ];
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
            'canViewNewsFeed' => $this->canViewNewsFeed(),
            'newsFeedSources' => Inertia::defer(fn () => $this->indexNewsFeedSources(), 'post-news-feed'),
            'selectedNewsFeedSource' => request()->input('source'),
            'newsFeedPosts' => Inertia::defer(fn () => $this->indexNewsFeedPosts(), 'post-news-feed'),
            'postCapabilities' => $this->postCapabilities(),
        ]);
    }
}
