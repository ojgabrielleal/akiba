<?php

namespace App\Http\Controllers\Private\Locution;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Onair;
use App\Models\SongRequest;

class ToggleSongRequestBoxStatusController extends Controller
{
    use HasFlashMessages;

    public function __invoke()
    {
        $this->authorize('toggle', SongRequest::class);

        $onair = Onair::live()->first();

        if (!$onair) {
            return $this->flashMessage('error');
        }

        $onair->update([
            'allows_song_requests' => !$onair->allows_song_requests,
        ]);

        return $this->flashMessage('save');
    }
}
