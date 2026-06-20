<?php

namespace App\Http\Controllers\Provisory;

use App\Actions\SongRequest\CreateSongRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Provisory\CreateSongRequestRequest;
use Inertia\Inertia;

use App\Models\Onair;

use App\Http\Resources\OnairResource;

class HomeController extends Controller
{
    private $render = 'provisory/Home';

    public function showOnair()
    {
        return OnairResource::collection(
            Onair::live()->with('program.host')->get()
        );
    }

    public function createSongRequest(CreateSongRequestRequest $request, CreateSongRequestAction $createSongRequestAction)
    {
        $createSongRequestAction->execute(
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
