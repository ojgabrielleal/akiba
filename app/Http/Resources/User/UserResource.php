<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Concerns\HasFormats;
use App\Http\Resources\RoleResource;

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
                'gender' => $this->gender,
                'roles' => $this->whenLoaded('roles', fn () => $this->roles
                    ->map(fn ($role) => [
                        'uuid' => $role->uuid,
                        'name' => $role->name,
                        'label' => $role->label,
                        'weight' => $role->weight,
                    ])
                    ->values()),
                'highest_role' => $this->relationLoaded('roles')
                    ? $this->roles->sortByDesc('weight')->first()
                    : null,
            ];
        }

        if ($this->format === 'team') {
            return [
                'uuid' => $this->uuid,
                'name' => $this->name,
                'nickname' => $this->nickname,
                'avatar' => $this->avatar,
                'birth_date' => $this->birth_date?->format('Y-m-d'),
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'bibliography' => $this->bibliography,
                'roles' => RoleResource::collection($this->whenLoaded('roles')),
                'socials' => UserSocialResource::collection($this->whenLoaded('socials')),
                'preferences' => [
                    'likes' => UserPreferenceResource::collection($this->whenLoaded('preferences', fn () => $this->preferences
                        ->filter(fn ($item) => $item->is_like)
                        ->values())),
                    'unlikes' => UserPreferenceResource::collection($this->whenLoaded('preferences', fn () => $this->preferences
                        ->filter(fn ($item) => !$item->is_like)
                        ->values())),
                ],
                'top_animes' => UserTopAnimeResource::collection($this->whenLoaded('topAnimes')),
            ];
        }

        return [
            'uuid' => $this->uuid,
            'is_virtual' => $this->is_virtual,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'avatar' => $this->avatar,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'bibliography' => $this->bibliography,
            'favorites' => UserFavoriteResource::collection($this->favorites),
            'top_animes' => UserTopAnimeResource::collection($this->whenLoaded('topAnimes')),
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
