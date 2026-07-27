<?php

namespace App\Http\Controllers\Public\Pages;

use App\Actions\PageView\StorePageViewAction;
use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Inertia\Inertia;

class ReadPageController extends Controller
{
    public function __construct(
        private PostFilter $postFilter,
        private StorePageViewAction $storePageViewAction,
    ) {}

    public function render(string $slug)
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->with(['author', 'references', 'tags', 'reactions', 'reviews.author'])
            ->firstOrFail();

        $this->storePageViewAction->execute($post, request());

        return Inertia::render('public/ReadPost', [
            'post' => PostResource::make($post),
            'relatedPosts' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'status' => 'published',
                    'module' => $post->module,
                    'with' => 'tags',
                    'order_by' => 'random',
                    'tag' => $post->tags->first()?->name,
                    'limit' => 5,
                    'except' => $post,
                    'ignore_authorization' => true,
                ])
            )->format('home-list'),
        ]);
    }
}
