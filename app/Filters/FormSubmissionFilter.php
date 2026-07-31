<?php

namespace App\Filters;

use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionFilter
{
    public function apply(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = FormSubmission::query()
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['status_order'] ?? false,
                fn (Builder $query) => $query->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 else 2 end")
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
