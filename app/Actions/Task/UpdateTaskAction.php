<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class UpdateTaskAction
{
    public function execute(Task $task, User $user, array $data): Task
    {
        return DB::transaction(function () use ($task, $user, $data) {
            $task->fill([
                'user_id' => $user->id,
                'title' => $data['title'],
                'dead_line' => $data['dead_line'],
                'description' => $data['description'] ?? null,
            ]);

            if ($task->isDirty()) {
                $task->save();
            }

            return $task;
        });
    }
}
