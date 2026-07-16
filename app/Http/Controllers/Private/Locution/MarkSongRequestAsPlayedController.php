<?php

namespace App\Http\Controllers\Private\Locution;

use App\Actions\Locution\MarkSongRequestAsPlayedAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;

class MarkSongRequestAsPlayedController extends Controller
{
    use HasFlashMessages;

    public function __invoke(SongRequest $songRequest, MarkSongRequestAsPlayedAction $markSongRequestAsPlayedAction)
    {
        $this->authorize('reproduce', $songRequest);

        $markSongRequestAsPlayedAction->execute($songRequest);

        return $this->flashMessage('complete');
    }
}
