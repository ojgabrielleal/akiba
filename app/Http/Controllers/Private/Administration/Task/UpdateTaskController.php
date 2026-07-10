<?php

namespace App\Http\Controllers\Private\Administration\Task;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;

class UpdateTaskController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateTaskRequest $request, Task $task)
    {
        $user = User::where('uuid', $request->input('user'))->firstOrFail();

        $task->fill([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'dead_line' => $request->input('dead_line'),
            'description' => $request->input('description'),
        ]);

        if ($task->isDirty()) {
            $task->save();
        }

        return $this->flashMessage('update');
    }
}
