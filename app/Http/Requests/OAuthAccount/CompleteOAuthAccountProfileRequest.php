<?php

namespace App\Http\Requests\OAuthAccount;

use App\Models\OAuthAccount;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOAuthAccountProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->attributes->get('oauth_account') instanceof OAuthAccount;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nickname' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
        ];
    }
}
