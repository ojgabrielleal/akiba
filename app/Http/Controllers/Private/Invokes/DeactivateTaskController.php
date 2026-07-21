<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Task\DeactivateTaskAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Task;

class DeactivateTaskController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivateTaskAction $deactivateTaskAction,
    ) {}

    public function __invoke(Task $task)
    {
        $this->authorize('deactivate', $task);

        $this->deactivateTaskAction->execute($task);

        return $this->flashMessage('deactivate');
    }
}
