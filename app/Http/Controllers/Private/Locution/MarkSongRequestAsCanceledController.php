<?php

namespace App\Http\Controllers\Private\Locution;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;

class MarkSongRequestAsCanceledController extends Controller
{
    use HasFlashMessages;

    public function __invoke(SongRequest $songRequest)
    {
        $this->authorize('cancel', $songRequest);

        $songRequest->update([
            'was_canceled' => true,
        ]);

        $songRequest->onair()->decrement('song_requests_total');

        return $this->flashMessage('update');
    }
}
