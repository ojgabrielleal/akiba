<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityService
{
    public function confirmActivityParticipant(Activity $activity, User $user): void
    {
        DB::transaction(fn () => $activity->confirmations()->attach($user->id));
    }

    public function store(User $user, array $data): Activity
    {
        return DB::transaction(function () use ($user, $data) {
            $mayHaveConfirmations = $data['purpose'] === 'activity';

            $activity = Activity::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'limit' => $data['limit'],
                'content' => $data['content'],
                'allows_confirmations' => $mayHaveConfirmations,
            ]);

            if ($mayHaveConfirmations) {
                $activity->calendar()->create([
                    'user_id' => $user->id,
                    'content' => $data['title'],
                    'hour' => $data['hour'],
                    'date' => $data['date'],
                    'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
                ]);
            }

            return $activity;
        });
    }

    public function update(Activity $activity, User $user, array $data): Activity
    {
        return DB::transaction(function () use ($activity, $user, $data) {
            $confirmationsAllowed = $data['purpose'] === 'activity';

            $activity->fill([
                'title' => $data['title'],
                'limit' => $data['limit'],
                'content' => $data['content'],
                'allows_confirmations' => $confirmationsAllowed,
            ]);

            if ($activity->isDirty()) {
                $activity->save();
            }

            if ($confirmationsAllowed) {
                $activity->calendar()->updateOrCreate(
                    ['activity_id' => $activity->id],
                    [
                        'user_id' => $user->id,
                        'content' => $data['title'],
                        'hour' => $data['hour'],
                        'date' => $data['date'],
                        'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
                    ]
                );
            }

            return $activity;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Activity::query()
            ->when(
                $filters['not_expired'] ?? false,
                fn (Builder $query) => $query->notExpired()
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
    }}
