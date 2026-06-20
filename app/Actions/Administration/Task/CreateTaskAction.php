<?php

namespace App\Actions\Administration\Task;

use App\Models\User;
use App\Models\Task;

class CreateTaskAction
{
    public function execute(User $user, array $data): Task
    {
        return Task::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'dead_line' => $data['dead_line'],
        ]);
    }
}
