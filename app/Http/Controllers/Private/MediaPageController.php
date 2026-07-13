<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Poll;

use App\Http\Resources\PollResource;

class MediaPageController extends Controller
{
    private $render = 'private/Media';

    public function render()
    {
        return Inertia::render($this->render, [
            'polls' => $this->indexPolls(),
            'latestPoll' => $this->latestValidPoll(),
        ]);
    }

    public function indexPolls()
    {
        $this->authorize('viewAny', Poll::class);

        return PollResource::collection(
            Poll::active()
                ->with([
                    'options.votes',
                    'votes.user',
                ])
                ->get()
        );
    }

    public function latestValidPoll()
    {
        $this->authorize('viewAny', Poll::class);

        $poll = Poll::active()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with([
                'options.votes',
                'votes.user',
            ])
            ->latest()
            ->first();

        return PollResource::make($poll);
    }

}
