<?php

namespace App\Http\Controllers\Public;

use App\Services\PostService;
use App\Services\CacheService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;

class EditorialController extends Controller
{
    public function __construct(
        private PostService $postFilter,
        private CacheService $cache,
    ) {}

    public function news()
    {
        $categories = ['news', 'anime', 'manga', 'light-novel', 'events'];
        $tag = request('tag', $categories[0]);

        return Inertia::render('public/Editorial', [
            'title' => 'News',
            'categories' => $categories,
            'activeTag' => $tag,
            'posts' => $this->indexNewsPosts($tag),
        ]);
    }

    public function columns()
    {
        $categories = ['tops', 'lists', 'reviews', 'first-impression', 'curiosities'];
        $tag = request('tag');

        return Inertia::render('public/Editorial', [
            'title' => 'Colunas',
            'categories' => $categories,
            'activeTag' => $tag,
            'posts' => $this->indexColumnPosts($categories, $tag),
        ]);
    }

    private function indexNewsPosts(string $tag)
    {
        return PostResource::collection(
            $this->cache->remember(['editorial', 'news', $tag, request()->query('page', 1)], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'post',
                'with' => 'tags',
                'order_by' => 'random',
                'order_direction' => 'desc',
                'tag' => $tag,
                'paginate' => 10,
                'ignore_authorization' => true,
            ]), 15, ['posts'])
        )->format('home-list');
    }

    private function indexColumnPosts(array $categories, ?string $tag)
    {
        $isReviewCategory = $tag === 'reviews';

        return PostResource::collection(
            $this->cache->remember(['editorial', 'columns', $tag, request()->query('page', 1)], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => $isReviewCategory ? 'review' : 'post',
                'with' => 'tags',
                'order_by' => $isReviewCategory ? 'metadata_date_of_release' : ($tag ? 'created_at' : 'random'),
                'order_direction' => 'desc',
                'tag' => $isReviewCategory ? null : $tag,
                'tags' => $tag ? null : $categories,
                'paginate' => 10,
                'ignore_authorization' => true,
            ]), 15, [$isReviewCategory ? 'reviews' : 'posts'])
        )->format('home-list');
    }
}
