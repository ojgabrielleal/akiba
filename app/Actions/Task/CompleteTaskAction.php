<?php

namespace App\Actions\Task;

use App\Models\Task;

use Illuminate\Support\Facades\DB;

class CompleteTaskAction
{
    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            if ($task->status === 'in_review') {
                $task->update(['status' => 'completed']);
            }

            return $task;
        });
    }
}
