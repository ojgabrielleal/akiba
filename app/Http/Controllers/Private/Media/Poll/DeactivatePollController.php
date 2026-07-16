<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Actions\Poll\DeactivatePollAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Poll;

class DeactivatePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Poll $poll, DeactivatePollAction $deactivatePollAction)
    {
        $this->authorize('delete', $poll);

        $deactivatePollAction->execute($poll);

        return $this->flashMessage('deactivate');
    }
}
