<?php

namespace App\Http\Resources\Post;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'author' => [
                'uuid' => $this->oauthAccount?->uuid,
                'name' => $this->oauthAccount?->nickname
                    ?? $this->oauthAccount?->username
                    ?? 'Akiba ID',
                'avatar' => $this->oauthAccount?->avatar,
            ],
        ];
    }
}
