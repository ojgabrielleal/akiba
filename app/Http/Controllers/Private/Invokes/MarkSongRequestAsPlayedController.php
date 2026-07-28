<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\MarkSongRequestAsPlayedAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\SongRequest;

class MarkSongRequestAsPlayedController extends Controller
{
    use HasFlashMessages;

    public function __invoke(MarkSongRequestAsPlayedAction $action, SongRequest $songRequest)
    {
        $this->authorize('markAsPlayed', $songRequest);

        $action->execute($songRequest);

        return $this->flashMessage('complete');
    }
}
