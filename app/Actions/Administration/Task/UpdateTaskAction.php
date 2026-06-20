<?php

namespace App\Actions\Administration\Task;

use App\Models\User;
use App\Models\Task;

class UpdateTaskAction
{
    public function execute(Task $task, User $user, array $data): Task
    {
        $task->fill([
            'user_id' => $user->id,
            'title' => $data['title'],
            'dead_line' => $data['dead_line'],
            'description' => $data['description'],
        ]);

        if ($task->isDirty()) {
            $task->save();
        }

        return $task;
    }
}
