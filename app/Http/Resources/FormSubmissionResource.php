<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'form_type' => $this->form_type,
            'name' => $this->name,
            'contact' => $this->contact,
            'subject' => $this->subject,
            'status' => $this->status,
            'payload' => $this->payload,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'reviewed_at' => $this->reviewed_at?->format('d/m/Y H:i'),
            'reviewer' => UserResource::make($this->whenLoaded('reviewer')),
            'comments' => FormSubmissionCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
