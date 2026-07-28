<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\PodcastFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\PodcastResource;

use App\Models\Podcast;

use Inertia\Inertia;

class PodcastPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Podcast';

    public function __construct(
        private PodcastFilter $podcastFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'podcasts' => $this->indexPodcasts(),
        ]);
    }

    private function indexPodcasts()
    {
        return $this->whenCanViewAny(Podcast::class,
            fn () => PodcastResource::collection(
                $this->podcastFilter->apply([
                    'active' => true,
                    'with' => 'author',
                    'paginate' => 10,
                ])
            ),
        );
    }
}
