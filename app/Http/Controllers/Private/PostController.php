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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                    'search' => request()->input('search'),
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


    public function storeEditorImage(Request $request, PostService $service): JsonResponse
    {
        abort_unless(
            $request->user()?->hasAnyPermission(['post.create', 'post.update', 'post.list.own']),
            403
        );

        $data = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ], [
            'image.required' => 'Selecione uma imagem.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'image.max' => 'A imagem precisa ter no máximo 5MB.',
        ]);

        return response()->json([
            'url' => $service->uploadImage('posts/content', $data['image']),
        ]);
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
                'search' => request()->input('search'),
                'paginate' => 10,
            ])
        )->format('grid');
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
            'canViewNewsFeed' => $this->canViewNewsFeed(),
            'newsFeedSources' => Inertia::defer(fn () => $this->indexNewsFeedSources(), 'post-news-feed'),
            'selectedNewsFeedSource' => request()->input('source'),
            'newsFeedPosts' => Inertia::defer(fn () => $this->indexNewsFeedPosts(), 'post-news-feed'),
        ]);
    }
}
