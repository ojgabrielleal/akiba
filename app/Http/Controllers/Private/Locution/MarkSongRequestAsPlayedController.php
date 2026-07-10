<?php

namespace App\Http\Controllers\Private\Locution;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;

class MarkSongRequestAsPlayedController extends Controller
{
    use HasFlashMessages;

    public function __invoke(SongRequest $songRequest)
    {
        $this->authorize('reproduce', $songRequest);

        $songRequest->update([
            'was_reproduced' => true,
        ]);

        $songRequest->onair()->increment('song_requests_total');

        return $this->flashMessage('complete');
    }
}
