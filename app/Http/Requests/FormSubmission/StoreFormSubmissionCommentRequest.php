<?php

namespace App\Http\Requests\FormSubmission;

use App\Http\Requests\LoggedWebRequest;

class StoreFormSubmissionCommentRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('form.submission.review') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:2000',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Escreva um comentário.',
            'comment.string' => 'O comentário precisa ser um texto.',
            'comment.max' => 'O comentário deve ter no máximo 2000 caracteres.',
        ];
    }
}
