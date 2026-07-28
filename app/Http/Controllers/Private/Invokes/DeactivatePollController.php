<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Poll\DeactivatePollAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Poll;

class DeactivatePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivatePollAction $action, Poll $poll)
    {
        $this->authorize('deactivate', $poll);

        $action->execute($poll);

        return $this->flashMessage('deactivate');
    }
}
