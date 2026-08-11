<?php

namespace App\Http\Controllers\Private;

use App\Services\PodcastService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\PodcastResource;

use App\Models\Podcast;

use Inertia\Inertia;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Podcast\StorePodcastRequest;
use App\Http\Requests\Podcast\UpdatePodcastRequest;

class PodcastController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Podcast';

    public function __construct(
        private PodcastService $podcastFilter,
    ) {}

    private function indexPodcasts()
    {
        return $this->whenCanViewAny(Podcast::class,
            fn () => PodcastResource::collection(
                $this->podcastFilter->filter([
                    'active' => true,
                    'with' => 'author',
                    'paginate' => 10,
                ])
            ),
        );
    }

    public function showPodcast(Podcast $podcast)
    {
        $this->authorize('view', $podcast);
        $this->authorize('viewAny', Podcast::class);

        return Inertia::render($this->render, [
            'podcast' => $this->indexPodcast($podcast),
            'podcasts' => $this->indexPodcasts(),
        ]);
    }

    public function storePodcast(StorePodcastRequest $request, PodcastService $service)
    {
        $service->store($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function updatePodcast(UpdatePodcastRequest $request, PodcastService $service, Podcast $podcast)
    {
        $service->update($podcast, $request->validated(), $request->file('image'));

        return $this->flashMessage('update');
    }

    public function deactivatePodcast(PodcastService $service, Podcast $podcast)
    {
        $this->authorize('deactivate', $podcast);

        $service->deactivate($podcast);

        return $this->flashMessage('deactivate');
    }

    private function indexPodcast(Podcast $podcast): PodcastResource
    {
        return new PodcastResource($podcast->load('author'));
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'podcasts' => $this->indexPodcasts(),
        ]);
    }
}
