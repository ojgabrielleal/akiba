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
