<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Task\DeactivateTaskAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Task;

class DeactivateTaskController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivateTaskAction $action, Task $task)
    {
        $this->authorize('deactivate', $task);

        $action->execute($task);

        return $this->flashMessage('deactivate');
    }
}
