<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;

use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\SongRequestResource;

class LocutionPageController extends Controller
{
    private $render = 'private/Locution';

    public function render()
    {
        return Inertia::render($this->render, [
            'programs' => $this->indexPrograms(),
            'onair' => $this->currentOnair(),
            'songRequests' => $this->indexSongRequests(),
        ]);
    }

    public function indexPrograms()
    {
        $this->authorize('viewAny', Program::class);

        return ProgramResource::collection(
            Program::availableForLocution(request()->user())->get()
        );
    }

    public function currentOnair()
    {
        $onair = Onair::live()->with('program.host')->first();

        return $onair ? new OnairResource($onair) : null;
    }

    public function indexSongRequests()
    {
        $this->authorize('viewAny', SongRequest::class);

        $onair = Onair::live()->first();
        return SongRequestResource::collection(
            SongRequest::where('onair_id', $onair->id)
                ->orderBy('created_at', 'asc')
                ->get()
        );
    }
}
