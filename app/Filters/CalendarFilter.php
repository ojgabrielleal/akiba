<?php

namespace App\Filters;

use App\Models\Calendar;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Calendar::query()
            ->when(
                $filters['upcoming'] ?? false,
                fn (Builder $query) => $query->upcoming()
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
