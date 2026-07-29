<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\LoggedWebRequest;

class TogglePostLikeRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
