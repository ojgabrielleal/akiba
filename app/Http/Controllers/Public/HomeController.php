<?php

namespace App\Http\Controllers\Public;

use App\Services\PodcastService;
use App\Services\PostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;
use App\Services\OAuthAccountService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\OAuthAccount\CompleteOAuthAccountProfileRequest;

class HomeController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private PodcastService $podcastFilter,
        private PostService $postFilter,
    ) {}

    private function indexFeaturedPosts()
    {
        return PostResource::collection(
            $this->postFilter->filter([
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
            $this->postFilter->filter([
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
            $this->postFilter->filter([
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
            $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'event',
                'event_date_from' => now()->toDateString(),
                'order_by' => 'metadata_event_date',
                'order_direction' => 'asc',
                'limit' => 5,
                'ignore_authorization' => true,
            ])
        )->format('home-list');
    }

    private function indexPodcasts()
    {
        return PodcastResource::collection(
            $this->podcastFilter->filter([
                'active' => true,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 3,
            ])
        );
    }

    public function updateOAuthAccountProfile(CompleteOAuthAccountProfileRequest $request, OAuthAccountService $service)
    {
        $service->update($request->attributes->get('oauth_account'), $request->validated());

        return $this->flashMessage('save', 'Perfil salvo com sucesso.');
    }

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
}
