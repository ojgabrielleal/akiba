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
                $filters['active'] ?? false,
                fn (Builder $query) => $query->active()
            )
            ->when(
                $filters['available_for_locution'] ?? null,
                fn (Builder $query, $user) => $query->availableForLocution($user)
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
