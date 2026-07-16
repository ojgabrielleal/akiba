<?php

namespace App\Http\Controllers\Private\Dashboard\Task;

use App\Actions\Task\MarkTaskToReviewAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Task;

class MarkTaskToReviewController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Task $task, MarkTaskToReviewAction $markTaskToReviewAction)
    {
        $this->authorize('update', $task);

        $markTaskToReviewAction->execute($task);

        return $this->flashMessage('complete');
    }
}
