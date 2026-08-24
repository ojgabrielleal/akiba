<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\LoggedWebRequest;

class StoreCommentRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:1000'],
            'parent_uuid' => ['nullable', 'string', 'exists:comments,uuid'],
        ];
    }
}
