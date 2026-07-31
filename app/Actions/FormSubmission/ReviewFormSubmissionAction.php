<?php

namespace App\Actions\FormSubmission;

use App\Models\FormSubmission;
use App\Models\User;

class ReviewFormSubmissionAction
{
    public function execute(FormSubmission $formSubmission, User $reviewer, string $status): FormSubmission
    {
        $formSubmission->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $formSubmission;
    }
}
