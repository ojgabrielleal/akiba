<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;

use App\Http\Resources\OnairResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\SongRequestResource;

use App\Actions\Locution\FinishLocutionAction;
use App\Actions\Locution\StartLocutionAction;

use App\Http\Requests\Locution\StartLocutionRequest;

class LocutionController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Locution';

    /*
     * ======================
     * PROGRAMS
     * ======================
     */

    public function indexPrograms()
    {
        if (request()->user()->cannot('list', Program::class)) {
            return null;
        }

        return ProgramResource::collection(
            Program::availableForLocution(request()->user())->get()
        );
    }

    /*
     * ======================
     * ONAIR
     * ======================
     */

    public function showOnair()
    {
        return new OnairResource(
            Onair::live()->with('program.host')->first()
        );
    }

    public function startLocution(StartLocutionRequest $request, StartLocutionAction $startLocutionAction, Program $program)
    {
        if ($request->user()->cannot('locution.start')) {
            return null;
        }

        $startLocutionAction->execute(
            $request->user(),
            $program,
            $request->all()
        );

        return $this->flashMessage('start');
    }

    public function finishLocution(FinishLocutionAction $finishLocutionAction)
    {
        if (request()->user()->cannot('locution.finish')) {
            return null;
        }

        $finishLocutionAction->execute();

        return $this->flashMessage('finish');
    }

    /*
     * ======================
     * SONG REQUESTS
     * ======================
     */

    public function indexSongRequests()
    {
        if (request()->user()->cannot('viewAny', SongRequest::class)) {
            return null;
        }

        $onair = Onair::live()->first();

        return SongRequestResource::collection(
            SongRequest::where('onair_id', $onair->id)
                ->orderBy('created_at', 'asc')
                ->get()
        );
    }

    public function markSongRequestAsPlayed(SongRequest $songRequest)
    {
        if (request()->user()->cannot('reproduce', $songRequest)) {
            return null;
        }

        $songRequest->update([
            'was_reproduced' => true,
        ]);

        $songRequest->onair()->increment('song_requests_total');

        return $this->flashMessage('order_fulfilled');
    }

    public function markSongRequestAsCanceled(SongRequest $songRequest)
    {
        if (request()->user()->cannot('cancel', $songRequest)) {
            return null;
        }

        $songRequest->update([
            'was_canceled' => true,
        ]);

        $songRequest->onair()->decrement('song_requests_total');

        return $this->flashMessage('order_canceled');
    }

    public function toggleSongRequestBoxStatus()
    {
        if (request()->user()->cannot('toggle', SongRequest::class)) {
            return null;
        }

        $onair = Onair::live()->first();

        $onair->update([
            'allows_song_requests' => !$onair->allows_song_requests,
        ]);

        return $this->flashMessage('save');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'programs' => $this->indexPrograms(),
            'onair' => $this->showOnair(),
            'songRequests' => $this->indexSongRequests(),
        ]);
    }
}
