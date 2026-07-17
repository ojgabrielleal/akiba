<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Podcast\DeactivatePodcastAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Podcast;

class DeactivatePodcastController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivatePodcastAction $deactivatePodcastAction,
    ) {}

    public function __invoke(Podcast $podcast)
    {
        $this->authorize('deactivate', $podcast);

        $this->deactivatePodcastAction->execute($podcast);

        return $this->flashMessage('deactivate');
    }
}
