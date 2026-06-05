<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'season' => $this->season,
            'episode' => $this->episode,
            'image' => $this->image,
            'summary' => $this->summary,
            'description' => $this->description,
            'audio' => $this->audio,
            'author' => UserResource::make($this->author)->format('compact'),
        ];
    }
}
