<?php

namespace App\Http\Controllers\Private;

use App\Actions\Task\StoreTaskAction;
use App\Actions\Task\UpdateTaskAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

use App\Http\Resources\TaskResource;

use App\Models\Task;

class TaskController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreTaskAction $storeTaskAction,
        private UpdateTaskAction $updateTaskAction,
    ) {}

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['responsible']));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->storeTaskAction->execute($request->validated());

        return $this->flashMessage('save');
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->updateTaskAction->execute($task, $request->validated());

        return $this->flashMessage('update');
    }
}
