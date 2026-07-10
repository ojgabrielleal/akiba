<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastResource;
use App\Models\Podcast;
use Inertia\Inertia;

class ShowPodcastController extends Controller
{
    private $render = 'private/Podcast';

    public function __invoke(Podcast $podcast)
    {
        $this->authorize('view', $podcast);

        return Inertia::render($this->render, [
            'podcasts' => PodcastResource::collection(
                Podcast::active()
                    ->latest()
                    ->with('author', 'views')
                    ->paginate(10)
            ),
            'podcast' => new PodcastResource($podcast->load('author')),
        ]);
    }
}
