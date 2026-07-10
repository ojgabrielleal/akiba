<?php

namespace App\Http\Controllers\Private\Media\Poll\Vote;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\PollOption;

class StoreVoteController extends Controller
{
    use HasFlashMessages;

    public function __invoke(PollOption $option)
    {
        $this->authorize('vote', $option);

        $option->increment('votes');

        return $this->flashMessage('save');
    }
}
