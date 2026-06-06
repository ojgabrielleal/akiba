<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'label' => $this->label,
            'name' => $this->name,
            'description' => $this->description,
            'weight' => $this->weight,
            'members_total' => $this->members()->count(),
            'permissions' => PermissionResource::collection($this->permissions),
        ];
    }
}
