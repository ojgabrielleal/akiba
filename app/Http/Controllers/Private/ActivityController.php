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

class ActivityController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreActivityAction $storeActivityAction,
        private UpdateActivityAction $updateActivityAction,
    ) {}

    public function show(Activity $activity)
    {
        $this->authorize('view', $activity);

        return new ActivityResource(
            $activity->load(['author', 'confirmations', 'calendar'])
        );
    }

    public function store(StoreActivityRequest $request)
    {
        $this->storeActivityAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->updateActivityAction->execute($activity, $request->user(), $request->validated());

        return $this->flashMessage('update');
    }
}
