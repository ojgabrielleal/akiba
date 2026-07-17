<?php

namespace App\Http\Requests\Poll;

use App\Http\Requests\LoggedWebRequest;

use App\Models\Poll;

class StorePollRequest extends LoggedWebRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Poll::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:published,revision,draft',
            'question' => 'required|unique:polls,question',
            'expires_at' => 'nullable|date|after:now',
            'options' => 'required|array|size:4',
            'options.*.uuid' => 'nullable|uuid',
            'options.*.option' => 'required|string',
        ];
    }
}
