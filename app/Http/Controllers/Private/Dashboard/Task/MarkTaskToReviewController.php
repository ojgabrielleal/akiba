<?php

namespace App\Http\Controllers\Private\Dashboard\Task;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Task;

class MarkTaskToReviewController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'in_review',
        ]);

        return $this->flashMessage('complete');
    }
}
