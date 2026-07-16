<?php

namespace App\Http\Controllers\Private\Dashboard\Activity;

use App\Actions\Activity\ConfirmActivityParticipantAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ConfirmActivityParticipantController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, Activity $activity, ConfirmActivityParticipantAction $confirmActivityParticipantAction)
    {
        $this->authorize('update', $activity);

        $confirmActivityParticipantAction->execute($activity, $request->user());

        return $this->flashMessage('save');
    }
}
