<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\FormSubmission\ReviewFormSubmissionAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\FormSubmission;

class ApproveFormSubmissionController extends Controller
{
    use HasFlashMessages;

    public function __invoke(FormSubmission $formSubmission, ReviewFormSubmissionAction $action)
    {
        $action->execute($formSubmission, auth()->user(), 'approved');

        return $this->flashMessage('update');
    }
}
