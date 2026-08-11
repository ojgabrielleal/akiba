<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function complete(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            if ($task->status === 'in_review') {
                $task->update(['status' => 'completed']);
            }

            return $task;
        });
    }

    public function deactivate(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task->update(['is_active' => false]);

            return $task;
        });
    }

    public function markTaskToReview(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            $task->update(['status' => 'in_review']);

            return $task;
        });
    }

    public function store(User $user, array $data): Task
    {
        return DB::transaction(function () use ($user, $data) {
            return Task::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'dead_line' => $data['dead_line'],
            ]);
        });
    }

    public function update(Task $task, User $user, array $data): Task
    {
        return DB::transaction(function () use ($task, $user, $data) {
            $task->fill([
                'user_id' => $user->id,
                'title' => $data['title'],
                'dead_line' => $data['dead_line'],
                'description' => $data['description'] ?? null,
            ]);

            if ($task->isDirty()) {
                $task->save();
            }

            return $task;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
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
    }}
