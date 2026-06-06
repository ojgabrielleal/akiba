<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'is_overdue' => $this->is_overdue,
            'days_remaining' => $this->days_remaining,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'responsible' => UserResource::make($this->responsible)->format('compact'),
        ];
    }
}
