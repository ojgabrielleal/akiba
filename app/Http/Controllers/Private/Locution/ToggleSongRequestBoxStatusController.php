<?php

namespace App\Http\Controllers\Private\Locution;

use App\Actions\Locution\ToggleSongRequestBoxStatusAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;

class ToggleSongRequestBoxStatusController extends Controller
{
    use HasFlashMessages;

    public function __invoke(ToggleSongRequestBoxStatusAction $toggleSongRequestBoxStatusAction)
    {
        $this->authorize('toggle', SongRequest::class);

        $onair = $toggleSongRequestBoxStatusAction->execute();

        if (!$onair) {
            return $this->flashMessage('error');
        }

        return $this->flashMessage('save');
    }
}
