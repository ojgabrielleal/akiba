<?php

namespace App\Http\Requests\FormSubmission;

use App\Http\Requests\LoggedWebRequest;
use Illuminate\Validation\Rule;

class StoreFormSubmissionRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_type' => ['required', 'string', Rule::in(['recruitment', 'complaint', 'contact'])],
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'payload' => ['required', 'array'],
            'payload.role' => ['nullable', 'string', Rule::in([
                'Locutor',
                'Redator',
                'Editor',
                'Podcaster',
                'Mídias Sociais / Marketing',
            ])],
            'payload.nickname' => ['nullable', 'string', 'max:255'],
            'payload.whatsapp' => ['nullable', 'string', 'max:255'],
            'payload.age' => ['nullable', 'integer', 'min:16', 'max:120'],
            'payload.portfolio' => ['nullable', 'string', 'max:1000'],
            'payload.interview_time' => ['nullable', 'string', 'max:255'],
            'payload.message' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
