<?php

namespace App\Http\Controllers\Private\Locution;

use App\Actions\Locution\MarkSongRequestAsCanceledAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\SongRequest;

class MarkSongRequestAsCanceledController extends Controller
{
    use HasFlashMessages;

    public function __invoke(SongRequest $songRequest, MarkSongRequestAsCanceledAction $markSongRequestAsCanceledAction)
    {
        $this->authorize('cancel', $songRequest);

        $markSongRequestAsCanceledAction->execute($songRequest);

        return $this->flashMessage('update');
    }
}
