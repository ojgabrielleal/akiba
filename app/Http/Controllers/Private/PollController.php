<?php

namespace App\Http\Controllers\Private;

use App\Actions\Poll\StorePollAction;
use App\Actions\Poll\UpdatePollAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Poll\StorePollRequest;
use App\Http\Requests\Poll\UpdatePollRequest;

use App\Models\Poll;

class PollController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StorePollAction $storePollAction,
        private UpdatePollAction $updatePollAction,
    ) {}

    public function store(StorePollRequest $request)
    {
        $this->storePollAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdatePollRequest $request, Poll $poll)
    {
        $this->updatePollAction->execute($poll, $request->validated());

        return $this->flashMessage('update');
    }
}
