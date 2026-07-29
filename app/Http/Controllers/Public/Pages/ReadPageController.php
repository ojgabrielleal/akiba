<?php

namespace App\Http\Controllers\Public\Pages;

use App\Actions\PageView\StorePageViewAction;
use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostCommentResource;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Inertia\Inertia;

class ReadPageController extends Controller
{
    public function __construct(
        private PostFilter $postFilter,
    ) {}

    public function render(StorePageViewAction $action, string $slug)
    {
        $post = $this->getPost($slug);

        $action->execute($post, request());

        return Inertia::render($this->componentFor($post), [
            'post' => $this->indexPost($post),
            'comments' => $this->indexComments($post),
            'relatedPosts' => $this->indexRelatedPosts($post),
        ]);
    }

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
            ->with(['author', 'references', 'tags', 'reactions', 'reviews.author'])
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
                ->with('oauthAccount')
                ->latest()
                ->paginate(10)
                ->withQueryString()
        );
    }

    private function indexRelatedPosts(Post $post)
    {
        return PostResource::collection(
            $this->postFilter->apply([
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
}
