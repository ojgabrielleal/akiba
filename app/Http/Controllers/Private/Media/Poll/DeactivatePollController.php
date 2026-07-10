<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Poll;

class DeactivatePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Poll $poll)
    {
        $this->authorize('delete', $poll);

        $poll->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }
}
