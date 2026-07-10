<?php

namespace App\Http\Controllers\Private\Administration\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class ShowTaskController extends Controller
{
    public function __invoke(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['responsible']));
    }
}
