<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\ToggleSongRequestBoxStatusAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\SongRequest;

class ToggleSongRequestBoxStatusController extends Controller
{
    use HasFlashMessages;

    public function __invoke(ToggleSongRequestBoxStatusAction $action)
    {
        $this->authorize('toggleBoxStatus', SongRequest::class);

        $onair = $action->execute();

        if (!$onair) {
            return $this->flashMessage('error');
        }

        return $this->flashMessage('save');
    }
}
