<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Actions\Podcast\DeactivatePodcastAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Podcast;

class DeactivatePodcastController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Podcast $podcast, DeactivatePodcastAction $deactivatePodcastAction)
    {
        $this->authorize('delete', $podcast);

        $deactivatePodcastAction->execute($podcast);

        return $this->flashMessage('deactivate');
    }
}
