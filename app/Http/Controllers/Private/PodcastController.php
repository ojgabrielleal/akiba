<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Podcast;

use App\Http\Resources\PodcastResource;

use App\Actions\Podcast\CreatePodcastAction;
use App\Actions\Podcast\UpdatePodcastAction;

use App\Http\Requests\Web\Podcast\CreatePodcastRequest;
use App\Http\Requests\Web\Podcast\UpdatePodcastRequest;

class PodcastController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Podcast';

    /*
     * ======================
     * PODCASTS
     * ======================
     */

    public function indexPodcasts()
    {
        if (request()->user()->cannot('viewAny', Podcast::class)) {
            return null;
        }

        return PodcastResource::collection(
            Podcast::active()
                ->with('author')
                ->paginate(10)
        );
    }

    public function showPodcast(Podcast $podcast)
    {
        if (request()->user()->cannot('view', $podcast)) {
            return null;
        }

        return Inertia::render($this->render, [
            'podcasts' => $this->indexPodcasts(),
            'podcast' => new PodcastResource($podcast->load('author')),
        ]);
    }

    public function createPodcast(CreatePodcastRequest $request, CreatePodcastAction $createPodcastAction)
    {
        $createPodcastAction->execute(
            $request->user(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function updatePodcast(UpdatePodcastRequest $request, Podcast $podcast, UpdatePodcastAction $updatePodcastAction)
    {
        $updatePodcastAction->execute(
            $podcast,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }

    public function deactivatePodcast(Podcast $podcast)
    {
        if (request()->user()->cannot('delete', $podcast)) {
            return null;
        }

        $podcast->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'podcasts' => $this->indexPodcasts(),
        ]);
    }
}
