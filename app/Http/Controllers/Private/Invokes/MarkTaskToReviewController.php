<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Task\MarkTaskToReviewAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Task;

class MarkTaskToReviewController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private MarkTaskToReviewAction $markTaskToReviewAction,
    ) {}

    public function __invoke(Task $task)
    {
        $this->authorize('markForReview', $task);

        $this->markTaskToReviewAction->execute($task);

        return $this->flashMessage('complete');
    }
}
