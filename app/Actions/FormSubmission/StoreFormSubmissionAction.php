<?php

namespace App\Actions\FormSubmission;

use App\Models\FormSubmission;

class StoreFormSubmissionAction
{
    public function execute(array $data): FormSubmission
    {
        return FormSubmission::create([
            'form_type' => $data['form_type'],
            'name' => $data['name'],
            'contact' => $data['contact'],
            'subject' => $data['subject'] ?? null,
            'payload' => $data['payload'],
        ]);
    }
}
