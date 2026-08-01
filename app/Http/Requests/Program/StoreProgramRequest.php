<?php

namespace App\Http\Requests\Program;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Program;

class StoreProgramRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Program::class) ?? false;
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
            'access_type' => 'required_unless:execution_mode,auto_dj|nullable|in:free,private',
            'execution_mode' => 'required|in:live,scheduled,playlist,auto_dj',
            'is_default_auto_dj' => 'nullable|boolean',
            'airtimes' => 'nullable|array',
            'airtimes.*.day' => 'required_with:airtimes|integer|min:0|max:6',
            'airtimes.*.hour' => 'required_with:airtimes|date_format:H:i:s',
            'schedules' => 'nullable|array',
            'schedules.*.scheduled_at' => 'required_with:schedules|date',
            'phrases' => 'nullable|array',
            'phrases.*.icon' => 'nullable|string',
            'phrases.*.text' => 'required_with:phrases|string',
            'phrases.*.decoration' => 'nullable|string',
            'phrases.*.texture' => 'nullable|string',
        ];
    }
}
