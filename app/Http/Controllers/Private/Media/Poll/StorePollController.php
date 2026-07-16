<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Actions\Poll\StorePollAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Poll\StorePollRequest;

class StorePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StorePollRequest $request, StorePollAction $storePollAction)
    {
        $storePollAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }
}
