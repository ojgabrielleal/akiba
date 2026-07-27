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
            'featuredPosts' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'status' => 'published',
                    'viewed_since' => now()->subWeek(),
                    'order_by' => 'views_count',
                    'order_direction' => 'desc',
                    'limit' => 3,
                    'ignore_authorization' => true,
                ])
            )->format('featured'),
            'latestReviews' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'status' => 'published',
                    'module' => 'review',
                    'order_by' => 'created_at',
                    'order_direction' => 'desc',
                    'limit' => 5,
                    'ignore_authorization' => true,
                ])
            )->format('featured'),
            'posts' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'status' => 'published',
                    'module' => 'post',
                    'with' => 'tags',
                    'order_by' => 'created_at',
                    'order_direction' => 'desc',
                    'limit' => 6,
                    'ignore_authorization' => true,
                ])
            )->format('home-list'),
            'events' => PostResource::collection(
                $this->postFilter->apply(request()->user(), [
                    'active' => true,
                    'status' => 'published',
                    'module' => 'event',
                    'order_by' => 'created_at',
                    'order_direction' => 'desc',
                    'limit' => 5,
                    'ignore_authorization' => true,
                ])
            )->format('home-list'),
            'podcasts' => PodcastResource::collection(
                $this->podcastFilter->apply([
                    'active' => true,
                    'order_by' => 'created_at',
                    'order_direction' => 'desc',
                    'limit' => 3,
                ])
            ),
        ]);
    }
}
