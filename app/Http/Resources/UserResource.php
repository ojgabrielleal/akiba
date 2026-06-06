<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use HasFormats;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->format === 'summary') {
            return [
                'uuid' => $this->uuid,
                'is_virtual' => $this->is_virtual,
                'name' => $this->name,
                'nickname' => $this->nickname,
                'avatar' => $this->avatar,
                'roles' => RoleResource::collection($this->roles),
            ];
        }

        return [
            'uuid' => $this->uuid,
            'is_virtual' => $this->is_virtual,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'avatar' => $this->avatar,
            'gender' => $this->gender,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'bibliography' => $this->bibliography,
            'favorites' => UserFavoriteResource::collection($this->favorites),
            'socials' => UserSocialResource::collection($this->socials),
            'preferences' => [
                'likes' => UserPreferenceResource::collection($this->preferences->filter(function ($item) {
                    return $item->is_like;
                })
                ->values()),

                'unlikes' => UserPreferenceResource::collection($this->preferences->filter(function ($item) {
                    return !$item->is_like;
                })
                ->values()),
            ],
            'roles' => RoleResource::collection($this->roles),
        ];
    }
}
