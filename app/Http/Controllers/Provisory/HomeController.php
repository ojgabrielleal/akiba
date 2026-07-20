<?php

namespace App\Http\Controllers\Provisory;

use App\Http\Controllers\Controller;

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

    public function render()
    {
        return Inertia::render($this->render, [
            'onair' => $this->showOnair(),
        ]);
    }
}
