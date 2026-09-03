<?php

namespace App\Http\Controllers\Private;

use App\Services\ListenerGalleryService;
use App\Services\PollService;
use App\Services\MysteryService;

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
use App\Http\Requests\Mystery\RespondMysteryInteractionRequest;
use App\Http\Requests\Mystery\StoreMysteryRequest;
use App\Http\Requests\Mystery\UpdateMysteryRequest;
use App\Http\Resources\MysteryResource;
use App\Models\Mystery;
use App\Models\MysteryInteraction;
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
        private MysteryService $mysteryFilter,
    ) {}

    private function indexPolls()
    {
        return $this->whenCanViewAny(Poll::class,
            fn () => PollResource::collection(
                $this->pollFilter->filter([
                    'active' => true,
                    'with_count' => 'votes',
                    'with' => $this->pollRelations(),
                ])
            ),
        );
    }

    private function indexLatestPoll()
    {
        return $this->whenCanViewAny(Poll::class,
            function () {
                $poll = $this->pollFilter->filter([
                    'open' => true,
                    'with_count' => 'votes',
                    'with' => $this->pollRelations(),
                    'first' => true,
                ]);

                return $poll ? PollResource::make($poll) : null;
            },
        );
    }

    private function indexListenerGalleries()
    {
        return $this->whenCanViewAny(ListenerGallery::class,
            fn () => ListenerGalleryResource::collection(
                $this->listenerGalleryFilter->filter(['paginate' => 20])
            ),
        );
    }

    private function indexMysteries()
    {
        return $this->whenCanViewAny(Mystery::class,
            fn () => MysteryResource::collection(
                $this->mysteryFilter->filter(['with' => ['author', 'interactions.participant', 'interactions.responder']])
            ),
        );
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

    public function storeMystery(StoreMysteryRequest $request, MysteryService $service)
    {
        $service->store($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updateMystery(UpdateMysteryRequest $request, MysteryService $service, Mystery $mystery)
    {
        $service->update($mystery, $request->validated());

        return $this->flashMessage('update');
    }

    public function publishMystery(MysteryService $service, Mystery $mystery)
    {
        $this->authorize('publish', $mystery);

        $service->publish($mystery);

        return $this->flashMessage('update');
    }

    public function deactivateMystery(MysteryService $service, Mystery $mystery)
    {
        $this->authorize('delete', $mystery);

        $service->deactivate($mystery);

        return $this->flashMessage('deactivate');
    }

    public function respondMysteryInteraction(RespondMysteryInteractionRequest $request, MysteryService $service, MysteryInteraction $mysteryInteraction)
    {
        $service->respond($mysteryInteraction, $request->user(), $request->validated());

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
            'mysteries' => $this->indexMysteries(),
        ]);
    }
}
