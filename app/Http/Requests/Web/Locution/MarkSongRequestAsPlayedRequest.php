<?php

namespace App\Http\Requests\Web\Locution;

use App\Http\Requests\Web\LoggedWebRequest;

class MarkSongRequestAsPlayedRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reproduce', $this->route('songRequest')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
