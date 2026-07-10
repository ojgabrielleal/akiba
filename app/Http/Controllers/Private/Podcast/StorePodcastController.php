<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Actions\Podcast\CreatePodcastAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Podcast\CreatePodcastRequest;

class StorePodcastController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreatePodcastRequest $request, CreatePodcastAction $createPodcastAction)
    {
        $createPodcastAction->execute(
            $request->user(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }
}
