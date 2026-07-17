<?php

namespace App\Http\Controllers\Private;

use App\Actions\Podcast\StorePodcastAction;
use App\Actions\Podcast\UpdatePodcastAction;

use App\Filters\PodcastFilter;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Podcast\StorePodcastRequest;
use App\Http\Requests\Podcast\UpdatePodcastRequest;

use App\Http\Resources\PodcastResource;

use App\Models\Podcast;

use Inertia\Inertia;

class PodcastController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Podcast';

    public function __construct(
        private PodcastFilter $podcastFilter,
        private StorePodcastAction $storePodcastAction,
        private UpdatePodcastAction $updatePodcastAction,
    ) {}

    public function show(Podcast $podcast)
    {
        $this->authorize('view', $podcast);
        $this->authorize('viewAny', Podcast::class);

        return Inertia::render($this->render, [
            'podcast' => new PodcastResource($podcast->load('author')),
            'podcasts' => PodcastResource::collection($this->podcastFilter->apply([
                'active' => true,
                'with' => 'author',
                'paginate' => 10,
            ])),
        ]);
    }

    public function store(StorePodcastRequest $request)
    {
        $this->storePodcastAction->execute(
            $request->user(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function update(UpdatePodcastRequest $request, Podcast $podcast)
    {
        $this->updatePodcastAction->execute(
            $podcast,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }
}
