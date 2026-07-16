<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Actions\Podcast\StorePodcastAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Podcast\StorePodcastRequest;

class StorePodcastController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StorePodcastRequest $request, StorePodcastAction $storePodcastAction)
    {
        $storePodcastAction->execute(
            $request->user(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }
}
