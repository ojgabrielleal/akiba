<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Poll\PollResource;
use App\Models\Poll;

class ShowPollController extends Controller
{
    public function __invoke(Poll $poll)
    {
        $this->authorize('view', $poll);

        $poll->loadCount('votes')->load([
            'options' => fn ($query) => $query
                ->withCount('votes')
                ->with('votes'),
            'votes.user',
        ]);

        return new PollResource($poll);
    }
}
