<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CalendarWeekResource extends ResourceCollection
{
    private const days = [
        0 => 'dom',
        1 => 'seg',
        2 => 'ter',
        3 => 'qua',
        4 => 'qui',
        5 => 'sex',
        6 => 'sáb',
    ];

    public function toArray(Request $request): array
    {
        $calendar = $this->collection->groupBy('day_of_week');

        return collect(self::days)->mapWithKeys(fn ($label, $day) => [
            $label => CalendarResource::collection($calendar->get($day, collect())),
        ])->all();
    }
}
