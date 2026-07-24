<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\OnairFilter;
use App\Filters\PostFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function __construct(
        private OnairFilter $onairFilter,
        private PostFilter $postFilter,
    ) {}

    public function render()
    {
        return Inertia::render('public/Home', [
            'onair' => OnairResource::collection(
                $this->onairFilter->apply([
                    'live' => true,
                    'with' => 'program.host',
                ])
            ),
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
        ]);
    }
}
