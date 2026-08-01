<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\User\UserResource;
use App\Models\OAuthAccount;
use App\Models\User;
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
        $author = $this->author;

        return [
            'uuid' => $this->uuid,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'author' => $this->author($author),
        ];
    }

    private function author(User|OAuthAccount|null $author): array
    {
        if ($author instanceof User) {
            $resource = UserResource::make($author)->format('summary')->resolve();

            return [
                'type' => 'member',
                'uuid' => $resource['uuid'] ?? null,
                'name' => $resource['nickname'] ?? $resource['name'] ?? 'Akiba ID',
                'avatar' => $resource['avatar'] ?? null,
                'gender' => $resource['gender'] ?? null,
            ];
        }

        return [
            'type' => $author instanceof OAuthAccount ? 'oauth' : null,
            'uuid' => $author?->uuid,
            'name' => $author?->nickname
                ?? $author?->username
                ?? 'Akiba ID',
            'avatar' => $author?->avatar,
            'gender' => null,
        ];
    }
}
