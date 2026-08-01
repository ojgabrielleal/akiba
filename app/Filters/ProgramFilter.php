<?php

namespace App\Filters;

use App\Models\Program;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Program::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['available_for_locution'] ?? null,
                fn (Builder $query, $user) => $query->availableForLocution($user)
            )
            ->when(
                $filters['execution_mode'] ?? null,
                fn (Builder $query, string $executionMode) => $query->where('execution_mode', $executionMode)
            )
            ->when(
                $filters['public_schedule'] ?? false,
                fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->where('access_type', 'private')
                        ->whereHas('programAirtimes');
                })
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike('name', '%'.trim($search).'%')
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage)->withQueryString(),
            fn (Builder $query) => $query->get()
        );
    }
}
