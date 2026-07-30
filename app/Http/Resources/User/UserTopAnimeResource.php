<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTopAnimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'position' => $this->position,
            'anime_theme_list_id' => $this->anime_theme_list_id,
            'slug' => $this->slug,
            'name' => $this->name,
            'image' => $this->image,
            'metadata' => $this->metadata,
        ];
    }
}
