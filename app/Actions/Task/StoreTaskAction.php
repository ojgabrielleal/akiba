<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class StoreTaskAction
{
    public function execute(User $user, array $data): Task
    {
        return DB::transaction(function () use ($user, $data) {
            return Task::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'dead_line' => $data['dead_line'],
            ]);
        });
    }
}
