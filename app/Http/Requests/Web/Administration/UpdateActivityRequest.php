<?php

namespace App\Http\Requests\Web\Administration;

use App\Http\Requests\Web\LoggedWebRequest;

class UpdateActivityRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('activity')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose' => 'required|in:notice,activity',
            'title' => 'required|string|max:255',
            'limit' => 'required|date',
            'content' => 'required|string',
            'hour' => 'required_if:purpose,activity|nullable|date_format:H:i',
            'date' => 'required_if:purpose,activity|nullable|date',
        ];
    }
}
