<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\LoggedWebRequest;

class UpdatePublicMemberProfileRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        $user = $this->user() ?? $this->attributes->get('member_user');

        return $user?->hasPermission('user.view.own') && $user->hasPermission('user.update.own');
    }

    public function rules(): array
    {
        return [
            'avatar' => 'nullable|image|max:4096',
            'nickname' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
        ];
    }
}
