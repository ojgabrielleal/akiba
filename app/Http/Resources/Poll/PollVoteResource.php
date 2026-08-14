<?php

namespace App\Http\Resources\Poll;

use App\Http\Resources\User\UserResource;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollVoteResource extends JsonResource
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
            'user' => $this->voter instanceof User
                ? UserResource::make($this->voter)->format('summary')
                : [
                    'uuid' => $this->voter?->uuid,
                    'nickname' => $this->voter?->nickname,
                    'avatar' => $this->voter?->avatar,
                ],
            'created_at' => $this->created_at,
        ];
    }
}
