<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\OnairFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Onair\OnairResource;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function __construct(
        private OnairFilter $onairFilter,
    ) {}

    public function render()
    {
        return Inertia::render('public/Home', [
            'onair' => OnairResource::collection(
                $this->onairFilter->apply([
                    'live' => true,
                    'with' => 'program.host',
                ])
            ),
        ]);
    }
}
