<?php

namespace App\Services;

use App\Models\RadioStation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AudienceService
{
    public function store(array $data): RadioStation
    {
        return RadioStation::create($data + ['is_active' => true]);
    }

    public function update(RadioStation $radioStation, array $data): RadioStation
    {
        $radioStation->update($data + ['is_active' => true]);

        return $radioStation;
    }

    public function destroy(RadioStation $radioStation): bool
    {
        return DB::transaction(function () use ($radioStation): bool {
            $radioStation->audienceSnapshots()->delete();

            return (bool) $radioStation->delete();
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = RadioStation::query()
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
            );

        $stations = $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );

        if (($filters['order_by_audience'] ?? false) && $stations instanceof Collection) {
            return $stations
                ->sortByDesc(fn (RadioStation $station) => $station->latestAudienceSnapshot?->listeners ?? -1)
                ->values();
        }

        return $stations;
    }

    public function history(array $filters = []): Collection
    {
        $period = $filters['period'] ?? 'day';

        $startsAt = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'semester' => now()->subMonths(6),
            default => now()->subDay(),
        };
        $bucket = $this->historyBucketExpression($period);

        return RadioStation::query()
            ->where('is_active', true)
            ->with(['audienceSnapshots' => fn ($query) => $query
                ->selectRaw("MIN(id) as id, radio_station_id, ROUND(AVG(listeners)) as listeners, 'online' as status, 0 as response_time_ms, {$bucket} as created_at, {$bucket} as updated_at")
                ->where('created_at', '>=', Carbon::instance($startsAt))
                ->whereNotNull('listeners')
                ->groupBy('radio_station_id')
                ->groupByRaw($bucket)
                ->orderBy('created_at')
            ])
            ->orderBy('name')
            ->get();
    }

    private function historyBucketExpression(string $period): string
    {
        return match ($period) {
            'week' => "STR_TO_DATE(CONCAT(DATE_FORMAT(created_at, '%Y-%m-%d '), LPAD(FLOOR(HOUR(created_at) / 6) * 6, 2, '0'), ':00:00'), '%Y-%m-%d %H:%i:%s')",
            'month' => 'DATE(created_at)',
            'semester' => 'DATE_SUB(DATE(created_at), INTERVAL WEEKDAY(created_at) DAY)',
            default => "STR_TO_DATE(DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00'), '%Y-%m-%d %H:%i:%s')",
        };
    }
}
