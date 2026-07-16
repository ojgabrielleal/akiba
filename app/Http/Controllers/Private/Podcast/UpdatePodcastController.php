<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Actions\Podcast\UpdatePodcastAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Podcast\UpdatePodcastRequest;
use App\Models\Podcast;

class UpdatePodcastController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdatePodcastRequest $request, Podcast $podcast, UpdatePodcastAction $updatePodcastAction)
    {
        $updatePodcastAction->execute(
            $podcast,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }
}
