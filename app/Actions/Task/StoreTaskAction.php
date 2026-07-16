<?php

namespace App\Actions\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreTaskAction
{
    public function execute(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $user = User::where('uuid', $data['user'])->firstOrFail();

            return Task::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'dead_line' => $data['dead_line'],
            ]);
        });
    }
}
