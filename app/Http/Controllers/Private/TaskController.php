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
use App\Models\User;

use Inertia\Inertia;

class TaskController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return Inertia::render($this->render, [
            'task' => new TaskResource($task->load(['responsible'])),
        ]);
    }

    public function store(StoreTaskRequest $request, StoreTaskAction $action)
    {
        $action->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function update(UpdateTaskRequest $request, UpdateTaskAction $action, Task $task)
    {
        $action->execute(
            $task,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('update');
    }
}
