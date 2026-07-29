<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\PodcastFilter;
use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function __construct(
        private PodcastFilter $podcastFilter,
        private PostFilter $postFilter,
    ) {}

    public function render()
    {
        return Inertia::render('public/Home', [
            'featuredPosts' => $this->indexFeaturedPosts(),
            'latestReviews' => $this->indexLatestReviews(),
            'posts' => $this->indexPosts(),
            'events' => $this->indexEvents(),
            'podcasts' => $this->indexPodcasts(),
        ]);
    }

    private function indexFeaturedPosts()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'interacted_since' => now()->subDays(15),
                'order_by' => 'interactions_count',
                'order_direction' => 'desc',
                'limit' => 3,
                'ignore_authorization' => true,
            ])
        )->format('featured');
    }

    private function indexLatestReviews()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'review',
                'with_count' => 'likes',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 5,
                'ignore_authorization' => true,
            ])
        )->format('featured');
    }

    private function indexPosts()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'post',
                'with' => 'tags',
                'with_count' => 'likes',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 6,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexEvents()
    {
        return PostResource::collection(
            $this->postFilter->apply([
                    'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'event',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 5,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexPodcasts()
    {
        return PodcastResource::collection(
            $this->podcastFilter->apply([
                'active' => true,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 3,
            ])
        );
    }
}
