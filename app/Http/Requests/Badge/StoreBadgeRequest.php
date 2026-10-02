<?php

namespace App\Http\Requests\Badge;

use App\Http\Requests\LoggedWebRequest;
use App\Models\Badge;
use Illuminate\Validation\Rule;

class StoreBadgeRequest extends LoggedWebRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Badge::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|alpha_dash:ascii|unique:badges,code',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024',
            'type' => ['required', 'string', Rule::in([Badge::TYPE_FIXED, Badge::TYPE_STEALABLE, Badge::TYPE_SCHEDULED])],
            'source' => 'nullable|string|max:255',
            'trigger' => ['nullable', 'string', Rule::in(['enigmagame.most_wins', 'song_request.most_requests'])],
            'starts_at' => 'required_if:type,'.Badge::TYPE_SCHEDULED.'|nullable|date',
            'ends_at' => 'required_if:type,'.Badge::TYPE_SCHEDULED.'|nullable|date|after:starts_at',
            'audience' => [Rule::requiredIf(in_array($this->input('type'), [Badge::TYPE_FIXED, Badge::TYPE_SCHEDULED], true)), 'nullable', 'string', Rule::in(['all', 'target'])],
            'target_type' => ['required_if:audience,target', 'nullable', 'string', Rule::in(['user', 'oauth_account'])],
            'target_uuid' => 'required_if:audience,target|nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
