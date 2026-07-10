<?php

namespace App\Http\Controllers\Private\Administration\Task;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\CreateTaskRequest;
use App\Models\Task;
use App\Models\User;

class StoreTaskController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateTaskRequest $request)
    {
        $user = User::where('uuid', $request->input('user'))->firstOrFail();

        Task::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'dead_line' => $request->input('dead_line'),
        ]);

        return $this->flashMessage('save');
    }
}
