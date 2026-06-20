<?php

namespace App\Http\Requests\Web\Dashboard;

use App\Http\Requests\Web\LoggedWebRequest;

class MarkTaskToReviewRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('task')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
