<?php

namespace App\Http\Requests\Program;

use App\Http\Requests\LoggedWebRequest;

class UpdateProgramRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('program')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user' => 'required_if:access_type,private|nullable|exists:users,uuid',
            'name' => 'required|string|max:255',
            'image' => 'nullable',
            'access_type' => 'required|in:free,private',
            'execution_mode' => 'required|in:live,scheduled,playlist,auto_dj',
            'is_default_auto_dj' => 'nullable|boolean',
            'airtimes' => 'nullable|array',
            'airtimes.*.uuid' => 'nullable|string',
            'airtimes.*.day' => 'required_with:airtimes|integer|min:0|max:6',
            'airtimes.*.hour' => 'required_with:airtimes|date_format:H:i:s',
            'plans' => 'nullable|array',
            'plans.*.uuid' => 'nullable|string',
            'plans.*.scheduled_at' => 'required_with:plans|date',
            'phrases' => 'nullable|array',
            'phrases.*.icon' => 'nullable|string',
            'phrases.*.text' => 'required_with:phrases|string',
            'phrases.*.decoration' => 'nullable|string',
            'phrases.*.texture' => 'nullable|string',
        ];
    }
}
