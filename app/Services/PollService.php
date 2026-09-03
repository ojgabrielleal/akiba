<?php

namespace App\Services;

use App\Models\Poll;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PollService
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function deactivate(Poll $poll): Poll
    {
        $poll = DB::transaction(function () use ($poll) {
            $poll->update(['is_active' => false]);

            return $poll;
        });

        $this->cache->invalidatePolls();
        $this->cache->invalidateTrash();

        return $poll;
    }

    public function store(User $user, array $data): Poll
    {
        $poll = DB::transaction(function () use ($user, $data) {
            $poll = Poll::create([
                'user_id' => $user->id,
                'status' => $data['status'],
                'question' => $data['question'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            $poll->options()->createMany(
                collect($data['options'])
                    ->map(fn (array $option) => ['option' => $option['option']])
                    ->all()
            );

            return $poll;
        });

        $this->cache->invalidatePolls();

        return $poll;
    }

    public function storeVote(PollOption $option, Model $voter): PollVote
    {
        $vote = DB::transaction(function () use ($option, $voter) {
            $vote = $option->poll->votes()->firstOrNew([
                'voter_type' => $voter->getMorphClass(),
                'voter_id' => $voter->getKey(),
            ]);

            if (!$vote->exists) {
                $vote->poll_option_id = $option->id;
                $vote->save();
            }

            return $vote;
        });

        $this->cache->invalidatePolls();

        return $vote;
    }

    public function update(Poll $poll, array $data): Poll
    {
        $poll = DB::transaction(function () use ($poll, $data) {
            $poll->update([
                'status' => $data['status'],
                'question' => $data['question'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            $options = $poll->options->keyBy('uuid');

            foreach ($data['options'] as $option) {
                $options->get($option['uuid'])->update([
                    'option' => $option['option'],
                ]);
            }

            return $poll;
        });

        $this->cache->invalidatePolls();

        return $poll;
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator|Poll|null
    {
        $query = Poll::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['open'] ?? false,
                fn (Builder $query) => $query->open()
            )
            ->when(
                $filters['with_count'] ?? null,
                fn (Builder $query, array|string $relations) => $query->withCount($relations)
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array $relations) => $query->with($relations)
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
    }}
