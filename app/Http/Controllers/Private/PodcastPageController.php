<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Queries\Podcast\ListPodcastsQuery;

class PodcastPageController extends Controller
{
    private $render = 'private/Podcast';

    public function render(ListPodcastsQuery $podcasts)
    {
        return Inertia::render($this->render, [
            'podcasts' => $podcasts->handle(request()->user()),
        ]);
    }
}
