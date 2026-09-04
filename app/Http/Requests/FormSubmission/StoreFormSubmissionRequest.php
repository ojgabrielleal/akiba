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
        $phoneRules = ['string', 'max:20', 'regex:/^\+?[0-9\s().-]{10,20}$/'];

        return [
            'form_type' => ['required', 'string', Rule::in(['recruitment', 'complaint', 'contact', 'event_registration'])],
            'name' => [Rule::requiredIf($this->input('form_type') !== 'event_registration'), 'string', 'max:255'],
            'contact' => [Rule::requiredIf($this->input('form_type') !== 'event_registration'), 'string', 'max:255'],
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
            'payload.whatsapp' => ['nullable', ...$phoneRules],
            'payload.age' => ['nullable', 'integer', 'min:16', 'max:120'],
            'payload.portfolio' => ['nullable', 'string', 'max:1000'],
            'payload.interview_time' => ['nullable', 'string', 'max:255'],
            'payload.message' => ['nullable', 'string', 'max:5000'],
            'payload.event_uuid' => ['nullable', 'string', 'max:255'],
            'payload.event_title' => ['nullable', 'string', 'max:255'],
            'payload.event_name' => [Rule::requiredIf($this->input('form_type') === 'event_registration'), 'string', 'max:255'],
            'payload.city' => [Rule::requiredIf($this->input('form_type') === 'event_registration'), 'string', 'max:255'],
            'payload.state' => [Rule::requiredIf($this->input('form_type') === 'event_registration'), 'string', 'size:2'],
            'payload.social_links' => [Rule::requiredIf($this->input('form_type') === 'event_registration'), 'string', 'max:1000'],
        ];
    }
}
