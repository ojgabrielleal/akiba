<?php

namespace App\Http\Controllers\Public;

use App\Services\PodcastService;
use App\Services\PollService;
use App\Services\PostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Http\Resources\Poll\PollResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;
use App\Services\OAuthAccountService;
use App\Services\ProfileService;
use App\Services\CacheService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\OAuthAccount\CompleteOAuthAccountProfileRequest;
use App\Http\Requests\Profile\UpdatePublicMemberProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private PodcastService $podcastFilter,
        private PollService $pollFilter,
        private PostService $postFilter,
        private CacheService $cache,
    ) {}

    private function indexFeaturedPosts()
    {
        return PostResource::collection(
            $this->cache->remember(['home', 'featured-posts'], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'interacted_since' => now()->subDays(15),
                'order_by' => 'interactions_count',
                'order_direction' => 'desc',
                'limit' => 3,
                'ignore_authorization' => true,
            ]), 15, ['posts'])
        )->format('featured');
    }

    private function indexLatestReviews()
    {
        return PostResource::collection(
            $this->cache->remember(['home', 'latest-reviews'], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'review',
                'with_count' => 'likes',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 5,
                'ignore_authorization' => true,
            ]), null, ['reviews'])
        )->format('featured');
    }

    private function indexPosts()
    {
        return PostResource::collection(
            $this->cache->remember(['home', 'posts'], fn () => $this->postFilter->filter([
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
            ]), null, ['posts'])
        )->format('home-list');
    }

    private function indexEvents()
    {
        return PostResource::collection(
            $this->cache->remember(['home', 'events', now()->toDateString()], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'event',
                'event_date_from' => now()->toDateString(),
                'order_by' => 'metadata_event_date',
                'order_direction' => 'asc',
                'limit' => 5,
                'ignore_authorization' => true,
            ]), null, ['events'])
        )->format('home-list');
    }

    private function indexPodcasts()
    {
        return PodcastResource::collection(
            $this->cache->remember(['home', 'podcasts'], fn () => $this->podcastFilter->filter([
                'active' => true,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 3,
            ]), null, ['podcasts'])
        );
    }

    private function indexLatestPoll()
    {
        $poll = $this->cache->remember(['home', 'latest-poll'], fn () => $this->pollFilter->filter([
            'active' => true,
            'open' => true,
            'with' => [
                'votes',
                'options' => fn ($query) => $query->withCount('votes'),
            ],
            'with_count' => 'votes',
            'order_by' => 'created_at',
            'order_direction' => 'desc',
            'first' => true,
        ]), null, ['polls']);

        return $poll ? PollResource::make($poll) : null;
    }

    public function updateOAuthAccountProfile(CompleteOAuthAccountProfileRequest $request, OAuthAccountService $service)
    {
        $service->update($request->attributes->get('oauth_account'), $request->validated());

        return $this->flashMessage('save', 'Perfil salvo com sucesso.');
    }

    public function updateMemberProfile(UpdatePublicMemberProfileRequest $request, ProfileService $service)
    {
        $user = $request->user() ?? $request->attributes->get('member_user');

        $service->updatePublicMemberProfile($user, $request->validated(), $request->file('avatar'));

        return $this->flashMessage('save', 'Perfil salvo com sucesso.');
    }

    public function logoutMemberProfile(Request $request)
    {
        if ($request->user()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/site');
    }

    public function render()
    {
        return Inertia::render('public/Home', [
            'featuredPosts' => $this->indexFeaturedPosts(),
            'latestReviews' => $this->indexLatestReviews(),
            'posts' => $this->indexPosts(),
            'events' => $this->indexEvents(),
            'podcasts' => $this->indexPodcasts(),
            'latestPoll' => $this->indexLatestPoll(),
        ]);
    }
}
