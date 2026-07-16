<?php

namespace App\Http\Controllers\Private\Administration\Task;

use App\Actions\Task\UpdateTaskAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;

class UpdateTaskController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateTaskRequest $request, Task $task, UpdateTaskAction $updateTaskAction)
    {
        $updateTaskAction->execute($task, $request->validated());

        return $this->flashMessage('update');
    }
}
