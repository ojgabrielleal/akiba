<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Locution\MarkSongRequestAsCanceledAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\SongRequest;

class MarkSongRequestAsCanceledController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private MarkSongRequestAsCanceledAction $markSongRequestAsCanceledAction,
    ) {}

    public function __invoke(SongRequest $songRequest)
    {
        $this->authorize('markAsCanceled', $songRequest);

        $this->markSongRequestAsCanceledAction->execute($songRequest);

        return $this->flashMessage('update');
    }
}
