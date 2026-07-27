<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;
use Illuminate\Validation\Rule;

class StorePostReactionRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::in([
                'angry',
                'duvid',
                'content',
                'happy',
                'big-happy',
            ])],
        ];
    }
}
