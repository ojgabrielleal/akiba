<?php

namespace App\Http\Resources;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'type_label' => match ($this->type) {
                Badge::TYPE_STEALABLE => 'Roubável',
                Badge::TYPE_SCHEDULED => 'Programável',
                default => 'Fixo',
            },
            'source' => $this->source,
            'rule' => $this->rule,
            'trigger' => $this->rule['trigger'] ?? null,
            'starts_at' => $this->rule['starts_at'] ?? null,
            'ends_at' => $this->rule['ends_at'] ?? null,
            'audience' => $this->rule['audience'] ?? 'all',
            'target_type' => $this->rule['target_type'] ?? null,
            'target_uuid' => $this->rule['target_uuid'] ?? null,
            'is_active' => $this->is_active,
            'active_assignments_total' => $this->active_assignments_count,
            'assignments_total' => $this->assignments_count,
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
