<?php

namespace App\Http\Requests\Web\Locution;

use App\Models\SongRequest;
use App\Http\Requests\Web\LoggedWebRequest;

class ToggleSongRequestBoxStatusRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('toggle', SongRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
