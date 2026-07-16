<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostFeaturedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'model' => $this->model, 
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'image' => $this->image,
            'cover' => $this->cover,
            'views' => $this->views,
        ];
    }
}
