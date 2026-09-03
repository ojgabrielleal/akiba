<?php

namespace App\Http\Controllers\Public;

use App\Services\ListenerGalleryService;
use App\Services\PollService;
use App\Services\PostService;
use App\Services\MysteryService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListenerGalleryResource;
use App\Http\Resources\Poll\PollResource;
use App\Http\Resources\Post\PostResource;
use Inertia\Inertia;
use App\Models\PollOption;
use App\Models\Mystery;
use App\Http\Requests\Mystery\StoreMysteryInteractionRequest;
use App\Http\Resources\MysteryResource;
use App\Services\CacheService;
use App\Support\AuthenticatedMember;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        private ListenerGalleryService $listenerGalleryFilter,
        private PollService $pollFilter,
        private PostService $postFilter,
        private MysteryService $mysteryFilter,
        private CacheService $cache,
    ) {}

    private function indexEvents()
    {
        return PostResource::collection(
            $this->cache->remember(['media', 'events', now()->toDateString()], fn () => $this->postFilter->filter([
                'user' => request()->user(),
                'active' => true,
                'status' => 'published',
                'module' => 'event',
                'event_date_from' => now()->toDateString(),
                'order_by' => 'metadata_event_date',
                'order_direction' => 'asc',
                'limit' => 4,
                'ignore_authorization' => true,
            ]), null, ['events'])
        )->format('home-list');
    }

    private function indexListenerGallery()
    {
        return ListenerGalleryResource::collection(
            $this->cache->remember(['media', 'listener-gallery'], fn () => $this->listenerGalleryFilter->filter([
                'order_by' => 'created_at',
                'order_direction' => 'desc',
                'limit' => 5,
            ]), null, ['media'])
        );
    }

    private function indexLatestPoll()
    {
        $poll = $this->cache->remember(['media', 'latest-poll'], fn () => $this->pollFilter->filter([
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

    private function indexPolls()
    {
        return PollResource::collection(
            $this->cache->remember(['media', 'polls'], fn () => $this->pollFilter->filter([
                'active' => true,
                'open' => true,
                'with' => [
                    'votes',
                    'options' => fn ($query) => $query->withCount('votes'),
                ],
                'with_count' => 'votes',
                'order_by' => 'created_at',
                'order_direction' => 'desc',
            ]), null, ['polls'])
        );
    }

    public function votePollOption(Request $request, PollService $service, PollOption $option)
    {
        $voter = AuthenticatedMember::fromRequest($request);

        abort_unless($voter, 403);

        if ($request->user()) {
            $this->authorize('vote', $option);
        }

        abort_unless(
            $option->poll()
                ->active()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists(),
            403,
        );

        $service->storeVote($option, $voter);

        return back(303)->with('success', 'Voto registrado com sucesso.');
    }

    public function storeMysteryInteraction(StoreMysteryInteractionRequest $request, MysteryService $service, Mystery $mystery)
    {
        $participant = AuthenticatedMember::fromRequest($request);

        abort_unless($participant, 403);

        $service->interact($mystery, $participant, $request->validated());

        return back(303)->with('success', 'Interacao enviada com sucesso.');
    }

    public function render()
    {
        return Inertia::render('public/Media', [
            'events' => $this->indexEvents(),
            'listenerGallery' => $this->indexListenerGallery(),
            'polls' => $this->indexPolls(),
            'latestPoll' => $this->indexLatestPoll(),
            'mystery' => ($mystery = $this->cache->remember(['media', 'active-mystery'], fn () => $this->mysteryFilter->active(), null, ['mysteries'])) ? MysteryResource::make($mystery) : null,
        ]);
    }
}
