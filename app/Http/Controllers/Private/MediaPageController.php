<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Poll;

use App\Http\Resources\PollResource;

class MediaPageController extends Controller
{
    private $render = 'private/Media';

    public function render()
    {
        return Inertia::render($this->render, [
            'polls' => $this->indexPolls(),
        ]);
    }

    public function indexPolls()
    {
        $this->authorize('viewAny', Poll::class);

        return PollResource::collection(
            Poll::active()->get()
        );
    }

}
