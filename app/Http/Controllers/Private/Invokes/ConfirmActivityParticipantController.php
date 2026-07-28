<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Activity\ConfirmActivityParticipantAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Activity;

use Illuminate\Http\Request;

class ConfirmActivityParticipantController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, ConfirmActivityParticipantAction $action, Activity $activity)
    {
        $this->authorize('confirmParticipation', $activity);

        $action->execute($activity, $request->user());

        return $this->flashMessage('save');
    }
}
