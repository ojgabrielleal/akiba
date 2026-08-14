<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\User\UserResource;
use App\Models\OAuthAccount;
use App\Models\User;
use App\Support\AuthenticatedMember;
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
        $authenticated = AuthenticatedMember::fromRequest($request);

        return [
            'uuid' => $this->uuid,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'is_edited' => $this->updated_at?->gt($this->created_at) ?? false,
            'can_edit' => $this->belongsToAuthenticatedMember($authenticated),
            'can_delete' => $this->belongsToAuthenticatedMember($authenticated),
            'author' => $this->author($author),
            'replies' => $this->relationLoaded('replies')
                ? PostCommentResource::collection($this->replies)->resolve($request)
                : [],
        ];
    }

    private function belongsToAuthenticatedMember(?object $member): bool
    {
        if (! $member instanceof User && ! $member instanceof OAuthAccount) {
            return false;
        }

        return $this->author_type === $member->getMorphClass()
            && (int) $this->author_id === (int) $member->getKey();
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
