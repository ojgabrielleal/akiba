<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Podcast;

use App\Http\Resources\PodcastResource;

class PodcastPageController extends Controller
{
    private $render = 'private/Podcast';

    public function render()
    {
        return Inertia::render($this->render, [
            'podcasts' => $this->indexPodcasts(),
        ]);
    }

    public function indexPodcasts()
    {
        $this->authorize('viewAny', Podcast::class);

        return PodcastResource::collection(
            Podcast::active()
                ->latest()
                ->with('author', 'views')
                ->paginate(10)
        );
    }

}
