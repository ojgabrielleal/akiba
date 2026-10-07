<?php

namespace App\Http\Requests\Badge;

use App\Http\Requests\LoggedWebRequest;
use App\Models\Badge;
use App\Support\EasterEggs;
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
            'type' => ['required', 'string', Rule::in([Badge::TYPE_FIXED, Badge::TYPE_STEALABLE, Badge::TYPE_SCHEDULED, Badge::TYPE_ACHIEVEMENT])],
            'source' => ['required_if:type,'.Badge::TYPE_STEALABLE.','.Badge::TYPE_ACHIEVEMENT, 'nullable', 'string', 'max:255'],
            'trigger' => ['required_if:type,'.Badge::TYPE_STEALABLE.','.Badge::TYPE_ACHIEVEMENT, 'nullable', 'string', Rule::in(['enigmagame.most_wins', 'song_request.most_requests', 'poll.most_votes', 'enigmagame.correct_total', 'song_request.played_total', 'song_request.played_ordinal', 'podcast.all_listened', 'podcast.listened_total', 'easter_egg'])],
            'easter_egg' => ['required_if:trigger,easter_egg', 'nullable', 'string', Rule::in(EasterEggs::keys())],
            'threshold' => ['required_if:trigger,enigmagame.correct_total,song_request.played_total,song_request.played_ordinal,podcast.listened_total', 'nullable', 'integer', 'min:1'],
            'starts_at' => 'required_if:type,'.Badge::TYPE_SCHEDULED.'|nullable|date',
            'ends_at' => 'required_if:type,'.Badge::TYPE_SCHEDULED.'|nullable|date|after:starts_at',
            'daily_starts_at' => ['nullable', 'date_format:H:i', 'required_with:daily_ends_at'],
            'daily_ends_at' => ['nullable', 'date_format:H:i', 'required_with:daily_starts_at'],
            'audience' => ['nullable', 'string', Rule::in(['target'])],
            'targets' => ['nullable', 'array'],
            'targets.*' => ['nullable', 'string', 'max:300'],
            'is_active' => 'boolean',
        ];
    }
}
