<?php

namespace App\Http\Resources\Calendar;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'hour' => $this->hour->format('H:i:s'),
            'date' => $this->date->format('Y-m-d'),
            'content' => $this->content,
            'type' => $this->type,
            'day_of_week' => $this->day_of_week,
            'responsible' => UserResource::make($this->responsible)->format('compact'),
            'activity' => ActivityResource::make($this->whenLoaded('activity')),
        ];
    }
}
