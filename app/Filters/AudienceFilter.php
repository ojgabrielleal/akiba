<?php

namespace App\Filters;

use App\Models\RadioStation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AudienceFilter
{
    public function apply(array $filters = []): Collection
    {
        $stations = RadioStation::query()
            ->when(
                $filters['active'] ?? false,
                fn (Builder $query) => $query->where('is_active', true)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'asc'
            )
            ->get();

        if ($filters['order_by_audience'] ?? false) {
            return $stations
                ->sortByDesc(fn (RadioStation $station) => $station->latestAudienceSnapshot?->listeners ?? -1)
                ->values();
        }

        return $stations;
    }

    public function history(string $period = 'day'): Collection
    {
        $startsAt = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'semester' => now()->subMonths(6),
            default => now()->subDay(),
        };

        return RadioStation::query()
            ->where('is_active', true)
            ->with(['audienceSnapshots' => fn ($query) => $query
                ->where('created_at', '>=', Carbon::instance($startsAt))
                ->whereNotNull('listeners')
                ->orderBy('created_at')
            ])
            ->orderBy('name')
            ->get();
    }
}
