<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Podcast;

use App\Http\Resources\PodcastResource;

use App\Actions\Podcast\CreatePodcastAction;
use App\Actions\Podcast\UpdatePodcastAction;

use App\Http\Requests\Podcast\CreatePodcastRequest;

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
        if ($request->user()->cannot('create', Podcast::class)) {
            return null;
        }

        $createPodcastAction->execute(
            $request->user()->id,
            $request->all(),
            $request->file('image')
        );

        return $this->flashMessage('save');
    }

    public function updatePodcast(Request $request, Podcast $podcast, UpdatePodcastAction $updatePodcastAction)
    {
        if ($request->user()->cannot('update', $podcast)) {
            return null;
        }

        $updatePodcastAction->execute(
            $podcast,
            $request->all(),
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
