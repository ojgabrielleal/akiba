<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use App\Support\AuthenticatedMember;

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
        $listener = AuthenticatedMember::fromRequest($request);

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'href' => route('podcast.read', $this->slug),
            'season' => $this->season,
            'episode' => $this->episode,
            'image' => $this->image,
            'summary' => $this->summary,
            'description' => $this->description,
            'audio' => $this->audio,
            'listened' => $listener ? $this->listens()
                ->where('listener_type', $listener->getMorphClass())
                ->where('listener_id', $listener->getKey())
                ->exists() : false,
            'author' => UserResource::make($this->author),
        ];
    }
}
