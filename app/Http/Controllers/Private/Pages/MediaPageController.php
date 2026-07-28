<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\ListenerGalleryFilter;
use App\Filters\PollFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ListenerGalleryResource;
use App\Http\Resources\Poll\PollResource;

use App\Models\ListenerGallery;
use App\Models\Poll;

use Inertia\Inertia;

class MediaPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Media';

    public function __construct(
        private ListenerGalleryFilter $listenerGalleryFilter,
        private PollFilter $pollFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'polls' => $this->indexPolls(),
            'latestPoll' => $this->indexLatestPoll(),
            'listenerGalleries' => $this->indexListenerGalleries(),
        ]);
    }

    private function indexPolls()
    {
        return $this->whenCanViewAny(Poll::class,
            fn () => PollResource::collection(
                $this->pollFilter->apply([
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
                $poll = $this->pollFilter->apply([
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
                $this->listenerGalleryFilter->apply(['paginate' => 20])
            ),
        );
    }

    private function pollRelations(): array
    {
        return [
            'options' => fn ($query) => $query
                ->withCount('votes')
                ->with('votes'),
            'votes.user',
        ];
    }
}
