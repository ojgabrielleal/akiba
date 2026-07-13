<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Http\Controllers\Controller;
use App\Http\Resources\PollResource;
use App\Models\Poll;

class ShowPollController extends Controller
{
    public function __invoke(Poll $poll)
    {
        $this->authorize('view', $poll);

        $poll->load([
            'options.votes',
            'votes.user',
        ]);

        return new PollResource($poll);
    }
}
