<?php

namespace App\Http\Requests\Web\Locution;

use App\Http\Requests\Web\LoggedWebRequest;

class MarkSongRequestAsCanceledRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancel', $this->route('songRequest')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
