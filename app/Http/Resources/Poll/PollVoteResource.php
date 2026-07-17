<?php

namespace App\Http\Resources\Poll;

use App\Http\Resources\User\UserResource;

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
            'user' => UserResource::make($this->user)->format('summary'),
            'created_at' => $this->created_at,
        ];
    }
}
