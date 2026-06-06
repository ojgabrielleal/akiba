<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'slug' => $this->slug,
            'type' => 'event',
            'cover' => $this->cover,
            'image' => $this->image,
            'title' => $this->title,
            'content' => $this->content,
            'dates' => $this->dates,
            'address' => $this->address,
            'views' => $this->views_count,
            'author' => UserResource::make($this->author)->format('compact')
        ];
    }
}
