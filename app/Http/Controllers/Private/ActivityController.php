<?php

namespace App\Http\Controllers\Private;

use App\Actions\Activity\StoreActivityAction;
use App\Actions\Activity\UpdateActivityAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;

use App\Http\Resources\ActivityResource;

use App\Models\Activity;

use Inertia\Inertia;

class ActivityController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);
        
        return Inertia::render($this->render, [
            'activity' => new ActivityResource(
                $activity->load(['author', 'confirmations', 'calendar'])
            ),
        ]);
    }

    public function store(StoreActivityRequest $request, StoreActivityAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdateActivityRequest $request, UpdateActivityAction $action, Activity $activity)
    {
        $action->execute($activity, $request->user(), $request->validated());

        return $this->flashMessage('update');
    }
}
