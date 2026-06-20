<?php

namespace App\Http\Requests\Web\Radio;

use App\Http\Requests\Web\LoggedWebRequest;

class CreateProgramRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Program::class) ?? false;
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
            'image' => 'required|image',
            'access_type' => 'required|in:free,private',
            'execution_mode' => 'required|in:live,scheduled,playlist,auto_dj',
            'is_default_auto_dj' => 'nullable|boolean',
            'airtimes' => 'nullable|array',
            'airtimes.*.day' => 'required_with:airtimes|integer|min:0|max:6',
            'airtimes.*.hour' => 'required_with:airtimes|date_format:H:i',
            'plans' => 'nullable|array',
            'plans.*.scheduled_at' => 'required_with:plans|date',
            'phrases' => 'nullable|array',
            'phrases.*.icon' => 'nullable|string',
            'phrases.*.text' => 'required_with:phrases|string',
            'phrases.*.decoration' => 'nullable|string',
            'phrases.*.texture' => 'nullable|string',
        ];
    }
}
