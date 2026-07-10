<?php

namespace App\Http\Controllers\Private\Administration\Activity;

use App\Actions\Administration\Activity\UpdateActivityAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\UpdateActivityRequest;
use App\Models\Activity;

class UpdateActivityController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateActivityRequest $request, Activity $activity, UpdateActivityAction $updateActivityAction)
    {
        $updateActivityAction->execute($activity, $request->user(), $request->validated());

        return $this->flashMessage('update');
    }
}
