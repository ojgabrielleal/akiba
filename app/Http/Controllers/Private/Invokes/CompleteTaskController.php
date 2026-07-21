<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Task\CompleteTaskAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Task;

class CompleteTaskController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private CompleteTaskAction $completeTaskAction,
    ) {}

    public function __invoke(Task $task)
    {
        $this->authorize('complete', $task);

        $this->completeTaskAction->execute($task);

        return $this->flashMessage('complete');
    }
}
