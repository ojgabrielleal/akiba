<?php

namespace App\Actions\Task;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class MarkTaskToReviewAction
{
    public function execute(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task->update(['status' => 'in_review']);

            return $task;
        });
    }
}
