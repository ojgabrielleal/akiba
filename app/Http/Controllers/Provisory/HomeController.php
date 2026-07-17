<?php

namespace App\Http\Controllers\Provisory;

use App\Actions\SongRequest\StoreSongRequestAction;

use App\Http\Controllers\Controller;

use App\Http\Requests\SongRequest\StoreSongRequestRequest;

use App\Http\Resources\Onair\OnairResource;

use App\Models\Onair;

use Inertia\Inertia;

class HomeController extends Controller
{
    private $render = 'provisory/Home';

    public function showOnair()
    {
        return OnairResource::collection(
            Onair::live()->with('program.host')->get()
        );
    }

    public function createSongRequest(StoreSongRequestRequest $request, StoreSongRequestAction $storeSongRequestAction)
    {
        $storeSongRequestAction->execute(
            $request->all(), 
            $request->ip()
        );

        return back(303);
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'onair' => $this->showOnair(),
        ]);
    }
}
