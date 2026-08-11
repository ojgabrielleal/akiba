<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionService
{
    public function review(FormSubmission $formSubmission, User $reviewer, string $status): FormSubmission
    {
        $formSubmission->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $formSubmission;
    }

    public function store(array $data): FormSubmission
    {
        return FormSubmission::create([
            'form_type' => $data['form_type'],
            'name' => $data['name'],
            'contact' => $data['contact'],
            'subject' => $data['subject'] ?? null,
            'payload' => $data['payload'],
        ]);
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
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
    }}
