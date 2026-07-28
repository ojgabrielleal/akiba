<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\MarkSongRequestAsCanceledAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\SongRequest;

class MarkSongRequestAsCanceledController extends Controller
{
    use HasFlashMessages;

    public function __invoke(MarkSongRequestAsCanceledAction $action, SongRequest $songRequest)
    {
        $this->authorize('markAsCanceled', $songRequest);

        $action->execute($songRequest);

        return $this->flashMessage('update');
    }
}
