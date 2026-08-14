<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'user' => UserResource::make($this->whenLoaded('user'))->format('summary'),
        ];
    }
}
