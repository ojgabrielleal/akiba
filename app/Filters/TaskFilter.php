<?php

namespace App\Filters;

use App\Models\Task;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Task::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['incomplete'] ?? false,
                fn (Builder $query) => $query->incomplete()
            )
            ->when(
                $filters['assigned_to'] ?? null,
                fn (Builder $query, $user) => $query->assignedTo($user)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['then_order_by'] ?? null,
                fn (Builder $query, string $column) => $query->orderBy(
                    $column,
                    $filters['then_order_direction'] ?? 'asc'
                )
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }
}
