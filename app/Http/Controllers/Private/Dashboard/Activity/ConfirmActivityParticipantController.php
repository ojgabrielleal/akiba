<?php

namespace App\Http\Controllers\Private\Dashboard\Activity;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ConfirmActivityParticipantController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->confirmations()->attach($request->user()->id);

        return $this->flashMessage('save');
    }
}
