<?php

namespace App\Http\Controllers\Public;

use App\Services\PageViewService;
use App\Services\PostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostCommentResource;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Models\PostComment;
use Inertia\Inertia;
use App\Http\Requests\Post\StorePostCommentRequest;
use App\Http\Requests\Post\StorePostReactionRequest;
use App\Http\Requests\Post\TogglePostLikeRequest;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;

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
        return PostCommentResource::collection(
            $post->comments()
                ->whereNull('parent_id')
                ->with(['author', 'replies.author'])
                ->latest()
                ->paginate(10)
                ->withQueryString()
        );
    }

    private function ensureCommentAuthor(PostComment $comment): void
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

    public function updateComment(StorePostCommentRequest $request, PostService $service, Post $post, PostComment $comment): RedirectResponse
    {
        abort_unless($comment->post_id === $post->id, 404);

        $this->ensureCommentAuthor($comment);

        $service->updateComment($comment, $request->safe()->only('comment'));

        return back(303);
    }

    public function deleteComment(Post $post, PostComment $comment, PostService $service): RedirectResponse
    {
        abort_unless($comment->post_id === $post->id, 404);

        $this->ensureCommentAuthor($comment);

        $service->deleteComment($comment);

        return back(303);
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
