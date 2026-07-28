<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Task\MarkTaskToReviewAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Task;

class MarkTaskToReviewController extends Controller
{
    use HasFlashMessages;

    public function __invoke(MarkTaskToReviewAction $action, Task $task)
    {
        $this->authorize('markForReview', $task);

        $action->execute($task);

        return $this->flashMessage('complete');
    }
}
