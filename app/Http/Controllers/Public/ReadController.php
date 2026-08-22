<?php

namespace App\Http\Controllers\Public;

use App\Services\PageViewService;
use App\Services\PostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Models\Comment;
use Inertia\Inertia;
use App\Http\Requests\Post\StorePostCommentRequest;
use App\Http\Requests\Post\StorePostReactionRequest;
use App\Http\Requests\Post\TogglePostLikeRequest;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;

class ReadController extends Controller
{
    public function __construct(
        private PostService $postFilter,
    ) {}

    private function componentFor(Post $post): string
    {
        return match ($post->module) {
            'review' => 'public/ReadReview',
            'event' => 'public/ReadEvent',
            default => 'public/ReadPost',
        };
    }

    private function getPost(string $slug): Post
    {
        return Post::query()
            ->where('slug', $slug)
            ->with(['author', 'references', 'tags', 'reactions', 'likes', 'reviews.author'])
            ->withCount('likes')
            ->firstOrFail();
    }

    private function indexPost(Post $post)
    {
        return PostResource::make($post)->format('public-read');
    }

    private function indexComments(Post $post)
    {
        $canModerate = request()->user()?->can('viewAny', Comment::class) ?? false;

        return CommentResource::collection(
            $post->comments()
                ->whereNull('parent_id')
                ->when(! $canModerate, fn (Builder $query) => $query->visible())
                ->with([
                    'author',
                    'replies' => fn ($query) => $query->when(! $canModerate, fn (Builder $query) => $query->visible()),
                    'replies.author',
                ])
                ->latest()
                ->paginate(10)
                ->withQueryString()
        );
    }

    private function ensureCommentAuthor(Comment $comment): void
    {
        $author = AuthenticatedMember::fromRequest(request());

        abort_unless(
            $author
                && $comment->author_type === $author->getMorphClass()
                && (int) $comment->author_id === (int) $author->getKey(),
            403
        );
    }

    private function indexRelatedPosts(Post $post)
    {
        return PostResource::collection(
            $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => $post->module,
                'with' => 'tags',
                'order_by' => 'random',
                'tag' => $post->tags->first()?->name,
                'limit' => 3,
                'except' => $post,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    public function storeReaction(StorePostReactionRequest $request, PostService $service, Post $post): RedirectResponse
    {
        $service->storeReaction($post, AuthenticatedMember::fromRequest($request), $request->validated('name'));

        return back(303);
    }

    public function toggleLike(TogglePostLikeRequest $request, PostService $service, Post $post): RedirectResponse
    {
        $service->toggleLike($post, AuthenticatedMember::fromRequest($request), hash('sha256', $request->session()->getId()));

        return back(303);
    }

    public function storeComment(StorePostCommentRequest $request, PostService $service, Post $post): RedirectResponse
    {
        $data = $request->validated();

        if ($parentUuid = $data['parent_uuid'] ?? null) {
            $data['parent_id'] = $post->comments()
                ->whereNull('parent_id')
                ->where('uuid', $parentUuid)
                ->value('id');

            abort_unless($data['parent_id'], 404);
        }

        unset($data['parent_uuid']);

        $service->storeComment($post, AuthenticatedMember::fromRequest($request), $data);

        return back(303);
    }

    public function updateComment(StorePostCommentRequest $request, PostService $service, Post $post, Comment $comment): RedirectResponse
    {
        abort_unless($comment->commentable_type === $post->getMorphClass() && (int) $comment->commentable_id === (int) $post->id, 404);

        $this->ensureCommentAuthor($comment);

        $service->updateComment($comment, $request->safe()->only('comment'));

        return back(303);
    }

    public function deleteComment(Post $post, Comment $comment, PostService $service): RedirectResponse
    {
        abort_unless($comment->commentable_type === $post->getMorphClass() && (int) $comment->commentable_id === (int) $post->id, 404);

        $this->ensureCommentAuthor($comment);

        $service->deleteComment($comment);

        return back(303);
    }

    public function approveComment(Post $post, Comment $comment, PostService $service): RedirectResponse
    {
        $this->ensurePostComment($post, $comment);
        $this->authorize('approve', $comment);

        $service->approveComment($comment, request()->user());

        return back(303);
    }

    public function hideComment(Post $post, Comment $comment, PostService $service): RedirectResponse
    {
        $this->ensurePostComment($post, $comment);
        $this->authorize('hide', $comment);

        $service->hideComment($comment, request()->user());

        return back(303);
    }

    public function restoreComment(Post $post, Comment $comment, PostService $service): RedirectResponse
    {
        $this->ensurePostComment($post, $comment);
        $this->authorize('restore', $comment);

        $service->restoreComment($comment, request()->user());

        return back(303);
    }

    public function destroyComment(Post $post, Comment $comment, PostService $service): RedirectResponse
    {
        $this->ensurePostComment($post, $comment);
        $this->authorize('delete', $comment);

        $service->deleteComment($comment);

        return back(303);
    }

    private function ensurePostComment(Post $post, Comment $comment): void
    {
        abort_unless(
            $comment->commentable_type === $post->getMorphClass()
                && (int) $comment->commentable_id === (int) $post->id,
            404
        );
    }

    public function render(PageViewService $service, string $slug)
    {
        $post = $this->getPost($slug);

        $service->store($post, request());

        return Inertia::render($this->componentFor($post), [
            'post' => $this->indexPost($post),
            'comments' => $this->indexComments($post),
            'relatedPosts' => $this->indexRelatedPosts($post),
        ]);
    }
}
