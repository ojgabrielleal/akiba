<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Models\Podcast;
use App\Queries\Podcast\ListPodcastsQuery;
use Inertia\Inertia;

class ShowPodcastController extends Controller
{
    private $render = 'private/Podcast';

    public function __invoke(Podcast $podcast, ListPodcastsQuery $podcasts)
    {
        $this->authorize('view', $podcast);

        return Inertia::render($this->render, [
            'podcast' => new PodcastResource($podcast->load('author')),
            'podcasts' => $podcasts->handle(request()->user()),
        ]);
    }
}
