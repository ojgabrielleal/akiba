<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarService
{
    public function store(User $user, array $data): Calendar
    {
        return DB::transaction(fn () => Calendar::create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'hour' => $data['hour'],
            'type' => $data['type'],
            'date' => $data['date'],
            'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
        ]));
    }

    public function update(Calendar $calendar, User $user, array $data): Calendar
    {
        return DB::transaction(function () use ($calendar, $user, $data) {
            $calendar->fill([
                'user_id' => $user->id,
                'content' => $data['content'],
                'hour' => $data['hour'],
                'type' => $data['type'],
                'date' => $data['date'],
                'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
            ]);

            if ($calendar->isDirty()) {
                $calendar->save();
            }

            return $calendar;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
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
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike('content', '%'.trim($search).'%')
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
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }}
