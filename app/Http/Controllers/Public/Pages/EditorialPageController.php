<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;

class EditorialPageController extends Controller
{
    public function __construct(
        private PostFilter $postFilter,
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

    public function reviews()
    {
        $sort = request('sort', 'alphabetical');

        return Inertia::render('public/Reviews', [
            'title' => 'Reviews',
            'activeSort' => $sort,
            'reviews' => $this->indexReviews($sort),
        ]);
    }

    private function indexNewsPosts(string $tag)
    {
        return PostResource::collection(
            $this->postFilter->apply([
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
            ])
        )->format('home-list');
    }

    private function indexColumnPosts(array $categories, ?string $tag)
    {
        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'post',
                'with' => 'tags',
                'order_by' => $tag ? 'created_at' : 'random',
                'order_direction' => 'desc',
                'tag' => $tag,
                'tags' => $tag ? null : $categories,
                'paginate' => 10,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexReviews(string $sort)
    {
        [$orderBy, $orderDirection] = match ($sort) {
            'likes' => ['likes_count', 'desc'],
            'year' => ['metadata_year_of_release', 'desc'],
            default => ['title', 'asc'],
        };

        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'review',
                'with_count' => 'likes',
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
                'paginate' => 15,
                'ignore_authorization' => true,
            ])
        )->format('featured');
    }
}
