<?php

namespace App\Http\Controllers\Private\Administration\Activity;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;

class ShowActivityController extends Controller
{
    public function __invoke(Activity $activity)
    {
        $this->authorize('view', $activity);

        return new ActivityResource(
            $activity->load(['author', 'confirmations', 'calendar'])
        );
    }
}
