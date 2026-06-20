<?php

namespace App\Http\Requests\Web\Media;

use App\Http\Requests\Web\LoggedWebRequest;

class CreateVoteRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pollOption')?->poll) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
