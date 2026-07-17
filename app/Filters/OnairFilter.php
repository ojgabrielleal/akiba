<?php

namespace App\Filters;

use App\Models\Onair;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OnairFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator|Onair|null
    {
        $query = Onair::query()
            ->when(
                $filters['live'] ?? false,
                fn (Builder $query) => $query->live()
            )
            ->when(
                $filters['execution_modes'] ?? null,
                fn (Builder $query, array $modes) => $query->whereIn('execution_mode', $modes)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        if ($filters['first'] ?? false) {
            return $query->first();
        }

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
