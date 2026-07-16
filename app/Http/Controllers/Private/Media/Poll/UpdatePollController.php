<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Actions\Poll\UpdatePollAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Poll\UpdatePollRequest;
use App\Models\Poll;

class UpdatePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdatePollRequest $request, Poll $poll, UpdatePollAction $updatePollAction)
    {
        $updatePollAction->execute($poll, $request->validated());

        return $this->flashMessage('update');
    }
}
