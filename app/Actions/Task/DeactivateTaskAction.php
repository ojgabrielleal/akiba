<?php

namespace App\Actions\Task;

use App\Models\Task;

use Illuminate\Support\Facades\DB;

class DeactivateTaskAction
{
    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task->update(['is_active' => false]);

            return $task;
        });
    }
}
