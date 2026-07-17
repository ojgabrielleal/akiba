<?php

namespace App\Http\Requests\Poll;

use App\Http\Requests\LoggedWebRequest;

use Illuminate\Validation\Rule;

class UpdatePollRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('poll')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $poll = $this->route('poll');

        return [
            'status' => 'required|string|in:published,revision,draft',
            'question' => 'required',
            'expires_at' => 'nullable|date|after:now',
            'options' => 'required|array',
        ];
    }
}
