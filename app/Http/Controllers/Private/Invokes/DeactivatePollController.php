<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Poll\DeactivatePollAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Poll;

class DeactivatePollController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivatePollAction $deactivatePollAction,
    ) {}

    public function __invoke(Poll $poll)
    {
        $this->authorize('deactivate', $poll);

        $this->deactivatePollAction->execute($poll);

        return $this->flashMessage('deactivate');
    }
}
