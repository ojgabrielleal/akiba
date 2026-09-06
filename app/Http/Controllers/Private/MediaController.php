<?php

namespace App\Http\Controllers\Private;

use App\Services\ListenerGalleryService;
use App\Services\PollService;
use App\Services\EnigmaGameService;
use App\Services\CacheService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ListenerGalleryResource;
use App\Http\Resources\Poll\PollResource;

use App\Models\ListenerGallery;
use App\Models\Poll;

use Inertia\Inertia;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\ListenerGallery\StoreListenerGalleryRequest;
use App\Http\Requests\ListenerGallery\UpdateListenerGalleryRequest;
use App\Http\Requests\Poll\StorePollRequest;
use App\Http\Requests\Poll\UpdatePollRequest;
use App\Http\Requests\EnigmaGame\RespondEnigmaGameInteractionRequest;
use App\Http\Requests\EnigmaGame\StoreEnigmaGameRequest;
use App\Http\Requests\EnigmaGame\UpdateEnigmaGameRequest;
use App\Http\Resources\EnigmaGameResource;
use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\PollOption;
use App\Support\AuthenticatedMember;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Media';

    public function __construct(
        private ListenerGalleryService $listenerGalleryFilter,
        private PollService $pollFilter,
        private EnigmaGameService $enigmagameFilter,
        private CacheService $cache,
    ) {}

    private function indexPolls()
    {
        return $this->whenCanViewAny(Poll::class,
            fn () => PollResource::collection(
                $this->cache->remember($this->mediaCacheKey('polls'), fn () => $this->pollFilter->filter([
                    'active' => true,
                    'with_count' => 'votes',
                    'with' => $this->pollRelations(),
                ]), null, ['polls'])
            ),
        );
    }

    private function indexLatestPoll()
    {
        return $this->whenCanViewAny(Poll::class,
            function () {
                $poll = $this->cache->remember($this->mediaCacheKey('latest-poll'), fn () => $this->pollFilter->filter([
                    'open' => true,
                    'with_count' => 'votes',
                    'with' => $this->pollRelations(),
                    'first' => true,
                ]), null, ['polls']);

                return $poll ? PollResource::make($poll) : null;
            },
        );
    }

    private function indexListenerGalleries()
    {
        return $this->whenCanViewAny(ListenerGallery::class,
            fn () => ListenerGalleryResource::collection(
                $this->cache->remember(
                    $this->mediaCacheKey('listener-galleries', ['page' => request()->query('page', 1)]),
                    fn () => $this->listenerGalleryFilter->filter(['paginate' => 20]),
                    null,
                    ['media']
                )
            ),
        );
    }

    private function indexEnigmaGames()
    {
        return $this->whenCanViewAny(EnigmaGame::class,
            fn () => EnigmaGameResource::collection(
                $this->cache->remember(
                    $this->mediaCacheKey('enigmagames'),
                    fn () => $this->enigmagameFilter->filter(['with' => ['author', 'interactions.participant', 'interactions.responder']]),
                    null,
                    ['enigmagames']
                )
            ),
        );
    }

    private function mediaCacheKey(string $scope, array $filters = []): array
    {
        return [
            'panel',
            'media',
            $scope,
            request()->user()->uuid,
            $filters,
        ];
    }

    private function pollRelations(): array
    {
        return [
            'options' => fn ($query) => $query
                ->withCount('votes')
                ->with('votes'),
            'votes.voter',
        ];
    }

    public function showListenerGallery(ListenerGallery $listenerGallery)
    {
        $this->authorize('view', $listenerGallery);

        return Inertia::render($this->render, [
            'listenerGallery' => $this->indexListenerGalleryItem($listenerGallery),
        ]);
    }

    public function storeListenerGallery(StoreListenerGalleryRequest $request, ListenerGalleryService $service)
    {
        $service->store($request->user(), $request->validated(), $request->file('image'));

        return $this->flashMessage('save');
    }

    public function updateListenerGallery(UpdateListenerGalleryRequest $request, ListenerGalleryService $service, ListenerGallery $listenerGallery)
    {
        $service->update($listenerGallery, $request->validated(), $request->file('image'));

        return $this->flashMessage('update');
    }

    public function destroyListenerGallery(ListenerGalleryService $service, ListenerGallery $listenerGallery)
    {
        $this->authorize('delete', $listenerGallery);

        $service->destroy($listenerGallery);

        return $this->flashMessage('delete');
    }

    public function storePoll(StorePollRequest $request, PollService $service)
    {
        $service->store($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updatePoll(UpdatePollRequest $request, PollService $service, Poll $poll)
    {
        $service->update($poll, $request->validated());

        return $this->flashMessage('update');
    }

    public function deactivatePoll(PollService $service, Poll $poll)
    {
        $this->authorize('deactivate', $poll);

        $service->deactivate($poll);

        return $this->flashMessage('deactivate');
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

        return $this->flashMessage('save');
    }

    public function storeEnigmaGame(StoreEnigmaGameRequest $request, EnigmaGameService $service)
    {
        $service->store($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateEnigmaGame(UpdateEnigmaGameRequest $request, EnigmaGameService $service, EnigmaGame $enigmagame)
    {
        $service->update($enigmagame, $request->validated());

        return $this->flashMessage('update');
    }

    public function publishEnigmaGame(EnigmaGameService $service, EnigmaGame $enigmagame)
    {
        $this->authorize('publish', $enigmagame);

        $service->publish($enigmagame);

        return $this->flashMessage('update');
    }

    public function deactivateEnigmaGame(EnigmaGameService $service, EnigmaGame $enigmagame)
    {
        $this->authorize('delete', $enigmagame);

        $service->deactivate($enigmagame);

        return $this->flashMessage('deactivate');
    }

    public function finishEnigmaGame(EnigmaGameService $service, EnigmaGame $enigmagame)
    {
        $this->authorize('delete', $enigmagame);

        $service->finish($enigmagame);

        return $this->flashMessage('finish');
    }

    public function respondEnigmaGameInteraction(RespondEnigmaGameInteractionRequest $request, EnigmaGameService $service, EnigmaGameInteraction $enigmagameInteraction)
    {
        $service->respond($enigmagameInteraction, $request->user(), $request->validated());

        return $this->flashMessage('update');
    }

    private function indexListenerGalleryItem(ListenerGallery $listenerGallery): ListenerGalleryResource
    {
        return new ListenerGalleryResource($listenerGallery);
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'polls' => $this->indexPolls(),
            'latestPoll' => $this->indexLatestPoll(),
            'listenerGalleries' => $this->indexListenerGalleries(),
            'enigmagames' => $this->indexEnigmaGames(),
        ]);
    }
}
