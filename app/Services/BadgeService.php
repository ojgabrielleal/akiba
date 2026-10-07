<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\EnigmaGameInteraction;
use App\Models\OAuthAccount;
use App\Models\PollVote;
use App\Models\Podcast;
use App\Models\PodcastListen;
use App\Models\SongRequest;
use App\Models\User;
use App\Processing\ImageProcess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BadgeService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function store(array $data, ?UploadedFile $image = null): Badge
    {
        $badge = DB::transaction(fn () => Badge::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image' => $image ? $this->image->store('badges', $image) : null,
            'type' => $data['type'],
            'source' => $data['source'] ?? null,
            'rule' => $this->ruleFromData($data),
            'is_active' => $data['is_active'] ?? true,
        ]));

        $this->awardConfiguredFixedBadge($badge);

        return $badge;
    }

    public function update(Badge $badge, array $data, ?UploadedFile $image = null): Badge
    {
        $badge = DB::transaction(function () use ($badge, $data, $image): Badge {
            $badge->fill([
                'code' => $data['code'] ?? $badge->code,
                'name' => $data['name'] ?? $badge->name,
                'description' => array_key_exists('description', $data) ? $data['description'] : $badge->description,
                'image' => $image ? $this->image->store('badges', $image, $badge->image) : $badge->image,
                'type' => $data['type'] ?? $badge->type,
                'source' => array_key_exists('source', $data) ? $data['source'] : $badge->source,
                'rule' => $this->ruleFromData($data, $badge->rule),
                'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : $badge->is_active,
            ]);

            if ($badge->isDirty()) {
                $badge->save();
            }

            return $badge;
        });

        $this->awardConfiguredFixedBadge($badge);

        return $badge;
    }

    private function awardConfiguredFixedBadge(Badge $badge): void
    {
        if ($badge->type !== Badge::TYPE_FIXED || ! $badge->is_active) {
            return;
        }

        foreach ($this->configuredFixedOwners($badge) as $owner) {
            $this->awardFixed(
                $badge->code,
                $owner,
                'Emblema fixo concedido pela administração.',
                ['trigger' => 'admin.fixed_award'],
            );
        }
    }

    private function configuredFixedOwners(Badge $badge): Collection
    {
        $audience = $badge->rule['audience'] ?? 'target';

        if ($audience !== 'target') {
            return collect();
        }

        $targets = $badge->rule['targets'] ?? [];

        if ($targets === [] && filled($badge->rule['target_type'] ?? null) && filled($badge->rule['target_uuid'] ?? null)) {
            $targets = [[
                'type' => $badge->rule['target_type'],
                'uuid' => $badge->rule['target_uuid'],
            ]];
        }

        return collect($targets)
            ->map(fn (array $target) => $this->ownerFromTarget($target['type'] ?? null, $target['uuid'] ?? null))
            ->filter()
            ->values();
    }

    private function ownerFromTarget(?string $targetType, ?string $targetUuid): ?Model
    {
        return match ($targetType) {
            'user' => User::query()->where('uuid', $targetUuid)->first(),
            'oauth_account' => OAuthAccount::query()->where('uuid', $targetUuid)->first(),
            default => null,
        };
    }

    public function destroy(Badge $badge): void
    {
        DB::transaction(function () use ($badge): void {
            $image = $badge->image;

            $badge->delete();

            if ($image) {
                $this->image->delete($image);
            }
        });
    }

    public function awardFixed(string $code, Model $owner, ?string $reason = null, array $metadata = [], ?Model $awardedBy = null): BadgeAssignment
    {
        return DB::transaction(function () use ($code, $owner, $reason, $metadata, $awardedBy): BadgeAssignment {
            $badge = $this->activeBadge($code, Badge::TYPE_FIXED);
            $assignment = BadgeAssignment::query()
                ->lockForUpdate()
                ->where('badge_id', $badge->id)
                ->where('owner_type', $owner->getMorphClass())
                ->where('owner_id', $owner->getKey())
                ->active()
                ->first();

            if ($assignment) {
                return $assignment;
            }

            return $this->createAssignment($badge, $owner, $reason, $metadata, $awardedBy);
        });
    }

    public function transferStealable(string $code, Model $owner, ?string $reason = null, array $metadata = [], ?Model $awardedBy = null): BadgeAssignment
    {
        return DB::transaction(function () use ($code, $owner, $reason, $metadata, $awardedBy): BadgeAssignment {
            $badge = $this->activeBadge($code, Badge::TYPE_STEALABLE);
            $currentAssignment = BadgeAssignment::query()
                ->lockForUpdate()
                ->where('badge_id', $badge->id)
                ->active()
                ->latest('acquired_at')
                ->first();

            if ($currentAssignment
                && $currentAssignment->owner_type === $owner->getMorphClass()
                && (string) $currentAssignment->owner_id === (string) $owner->getKey()) {
                return $currentAssignment;
            }

            if ($currentAssignment) {
                $currentAssignment->update([
                    'revoked_at' => now(),
                    'revoked_reason' => $reason,
                ]);
            }

            return $this->createAssignment($badge, $owner, $reason, $metadata, $awardedBy);
        });
    }


    public function awardScheduledPresence(Model $owner): Collection
    {
        $badges = Badge::query()
            ->active()
            ->where('type', Badge::TYPE_SCHEDULED)
            ->where('rule->trigger', 'site.presence_window')
            ->get()
            ->filter(fn (Badge $badge) => $this->scheduledBadgeIsOpen($badge))
            ->filter(fn (Badge $badge) => $this->scheduledBadgeTargetsOwner($badge, $owner));

        return $badges->map(fn (Badge $badge) => $this->awardScheduled(
            $badge,
            $owner,
            'Presença no site durante janela programada.',
            ['trigger' => 'site.presence_window'],
        ));
    }

    private function awardAchievement(Badge $badge, Model $owner, ?string $reason = null, array $metadata = []): BadgeAssignment
    {
        return DB::transaction(function () use ($badge, $owner, $reason, $metadata): BadgeAssignment {
            $badge = Badge::query()
                ->lockForUpdate()
                ->whereKey($badge->id)
                ->where('type', Badge::TYPE_ACHIEVEMENT)
                ->active()
                ->firstOrFail();

            $assignment = BadgeAssignment::query()
                ->lockForUpdate()
                ->where('badge_id', $badge->id)
                ->where('owner_type', $owner->getMorphClass())
                ->where('owner_id', $owner->getKey())
                ->active()
                ->first();

            if ($assignment) {
                return $assignment;
            }

            return $this->createAssignment($badge, $owner, $reason, $metadata, null);
        });
    }

    public function awardEnigmaGameCorrectAchievements(Model $owner): Collection
    {
        $correctTotal = EnigmaGameInteraction::query()
            ->where('type', EnigmaGameInteraction::TYPE_FINAL_ANSWER)
            ->where('result', 'correct')
            ->where('participant_type', $owner->getMorphClass())
            ->where('participant_id', $owner->getKey())
            ->count();

        return $this->awardThresholdAchievements(
            'enigmagame.correct_total',
            $owner,
            $correctTotal,
            'Meta de enigmas vencidos alcançada.',
        );
    }

    public function awardSongRequestPlayedAchievements(Model $owner): Collection
    {
        $playedTotal = SongRequest::query()
            ->where('type', 'music')
            ->where('was_reproduced', true)
            ->where('requester_type', $owner->getMorphClass())
            ->where('requester_id', $owner->getKey())
            ->count();

        return $this->awardThresholdAchievements(
            'song_request.played_total',
            $owner,
            $playedTotal,
            'Meta de pedidos atendidos alcançada.',
        );
    }

    public function awardSongRequestOrdinalAchievements(SongRequest $songRequest): Collection
    {
        if ($songRequest->type !== 'music' || ! $songRequest->was_reproduced || ! $songRequest->requester) {
            return collect();
        }

        $playedPosition = SongRequest::query()
            ->where('type', 'music')
            ->where('was_reproduced', true)
            ->where('id', '<=', $songRequest->id)
            ->count();

        $badges = Badge::query()
            ->active()
            ->achievement()
            ->where('rule->trigger', 'song_request.played_ordinal')
            ->get()
            ->filter(fn (Badge $badge) => (int) ($badge->rule['threshold'] ?? 0) === $playedPosition);

        return $badges->map(fn (Badge $badge) => $this->awardAchievement(
            $badge,
            $songRequest->requester,
            'Pedido musical atendido na posição configurada.',
            [
                'trigger' => 'song_request.played_ordinal',
                'threshold' => (int) ($badge->rule['threshold'] ?? 0),
                'position' => $playedPosition,
                'song_request_id' => $songRequest->id,
            ],
        ));
    }

    public function awardPodcastListenAchievements(Model $owner): Collection
    {
        $activePodcastIds = Podcast::query()
            ->active()
            ->pluck('id');

        $listenedTotal = PodcastListen::query()
            ->where('listener_type', $owner->getMorphClass())
            ->where('listener_id', $owner->getKey())
            ->whereIn('podcast_id', $activePodcastIds)
            ->count();

        $awards = $this->awardThresholdAchievements(
            'podcast.listened_total',
            $owner,
            $listenedTotal,
            'Meta de podcasts ouvidos alcançada.',
        );

        if ($activePodcastIds->isNotEmpty() && $listenedTotal >= $activePodcastIds->count()) {
            $awards = $awards->concat($this->awardAchievementsByTrigger(
                'podcast.all_listened',
                $owner,
                'Todos os podcasts ativos foram marcados como ouvidos.',
                [
                    'trigger' => 'podcast.all_listened',
                    'listened_total' => $listenedTotal,
                    'podcasts_total' => $activePodcastIds->count(),
                ],
            ));
        }

        return $awards->values();
    }

    public function awardEasterEggAchievements(string $easterEgg, Model $owner): Collection
    {
        $badges = Badge::query()
            ->active()
            ->achievement()
            ->where('rule->trigger', 'easter_egg')
            ->where('rule->easter_egg', $easterEgg)
            ->get();

        return $badges->map(fn (Badge $badge) => $this->awardAchievement(
            $badge,
            $owner,
            'Easter Egg descoberto.',
            ['trigger' => 'easter_egg', 'easter_egg' => $easterEgg],
        ));
    }

    private function awardThresholdAchievements(string $trigger, Model $owner, int $total, string $reason): Collection
    {
        $badges = Badge::query()
            ->active()
            ->achievement()
            ->where('rule->trigger', $trigger)
            ->get()
            ->filter(fn (Badge $badge) => $total >= (int) ($badge->rule['threshold'] ?? 0));

        return $badges->map(fn (Badge $badge) => $this->awardAchievement(
            $badge,
            $owner,
            $reason,
            [
                'trigger' => $trigger,
                'threshold' => (int) ($badge->rule['threshold'] ?? 0),
                'total' => $total,
            ],
        ));
    }

    private function awardAchievementsByTrigger(string $trigger, Model $owner, ?string $reason = null, array $metadata = []): Collection
    {
        $badges = Badge::query()
            ->active()
            ->achievement()
            ->where('rule->trigger', $trigger)
            ->get();

        return $badges->map(fn (Badge $badge) => $this->awardAchievement(
            $badge,
            $owner,
            $reason,
            $metadata ?: ['trigger' => $trigger],
        ));
    }

    private function awardScheduled(Badge $badge, Model $owner, ?string $reason = null, array $metadata = []): BadgeAssignment
    {
        return DB::transaction(function () use ($badge, $owner, $reason, $metadata): BadgeAssignment {
            $badge = Badge::query()
                ->lockForUpdate()
                ->whereKey($badge->id)
                ->where('type', Badge::TYPE_SCHEDULED)
                ->active()
                ->firstOrFail();

            $assignment = BadgeAssignment::query()
                ->lockForUpdate()
                ->where('badge_id', $badge->id)
                ->where('owner_type', $owner->getMorphClass())
                ->where('owner_id', $owner->getKey())
                ->active()
                ->first();

            if ($assignment) {
                return $assignment;
            }

            return $this->createAssignment($badge, $owner, $reason, $metadata, null);
        });
    }


    private function scheduledBadgeTargetsOwner(Badge $badge, Model $owner): bool
    {
        return true;
    }

    private function scheduledBadgeIsOpen(Badge $badge): bool
    {
        $startsAt = $badge->rule['starts_at'] ?? null;
        $endsAt = $badge->rule['ends_at'] ?? null;

        if (! $startsAt || ! $endsAt) {
            return false;
        }

        $now = now();

        if (! $now->betweenIncluded($startsAt, $endsAt)) {
            return false;
        }

        $dailyStartsAt = $badge->rule['daily_starts_at'] ?? null;
        $dailyEndsAt = $badge->rule['daily_ends_at'] ?? null;

        if (! $dailyStartsAt || ! $dailyEndsAt) {
            return true;
        }

        $currentTime = $now->format('H:i');

        if ($dailyStartsAt <= $dailyEndsAt) {
            return $currentTime >= $dailyStartsAt && $currentTime <= $dailyEndsAt;
        }

        return $currentTime >= $dailyStartsAt || $currentTime <= $dailyEndsAt;
    }

    public function transferStealableByTrigger(string $trigger, Model $owner, ?string $reason = null, array $metadata = [], ?Model $awardedBy = null): Collection
    {
        $badges = Badge::query()
            ->active()
            ->stealable()
            ->where('rule->trigger', $trigger)
            ->get();

        return $badges->map(fn (Badge $badge) => $this->transferStealable(
            $badge->code,
            $owner,
            $reason,
            $metadata,
            $awardedBy,
        ));
    }

    public function transferCompetitiveLeaderByTrigger(string $trigger, ?string $reason = null, array $metadata = [], ?Model $awardedBy = null): Collection
    {
        $leader = match ($trigger) {
            'enigmagame.most_wins' => $this->uniqueLeaderFromQuery(
                EnigmaGameInteraction::query()
                    ->select('participant_type as owner_type', 'participant_id as owner_id')
                    ->selectRaw('count(*) as total')
                    ->where('type', EnigmaGameInteraction::TYPE_FINAL_ANSWER)
                    ->where('result', 'correct')
                    ->whereNotNull('participant_type')
                    ->whereNotNull('participant_id')
                    ->groupBy('participant_type', 'participant_id')
            ),
            'song_request.most_requests' => $this->uniqueLeaderFromQuery(
                SongRequest::query()
                    ->select('requester_type as owner_type', 'requester_id as owner_id')
                    ->selectRaw('count(*) as total')
                    ->where('type', 'music')
                    ->whereNotNull('requester_type')
                    ->whereNotNull('requester_id')
                    ->groupBy('requester_type', 'requester_id')
            ),
            'poll.most_votes' => $this->uniqueLeaderFromQuery(
                PollVote::query()
                    ->select('voter_type as owner_type', 'voter_id as owner_id')
                    ->selectRaw('count(*) as total')
                    ->whereNotNull('voter_type')
                    ->whereNotNull('voter_id')
                    ->groupBy('voter_type', 'voter_id')
            ),
            default => null,
        };

        if (! $leader) {
            $this->revokeActiveStealableByTrigger($trigger, 'Nenhum líder único para este emblema competitivo.');

            return collect();
        }

        return $this->transferStealableByTrigger(
            $trigger,
            $leader,
            $reason,
            ['trigger' => $trigger, ...$metadata],
            $awardedBy,
        );
    }

    private function revokeActiveStealableByTrigger(string $trigger, ?string $reason = null): void
    {
        BadgeAssignment::query()
            ->whereHas('badge', fn (Builder $query) => $query
                ->stealable()
                ->where('rule->trigger', $trigger))
            ->active()
            ->get()
            ->each(fn (BadgeAssignment $assignment) => $this->revoke($assignment, $reason));
    }

    private function uniqueLeaderFromQuery(Builder $query): ?Model
    {
        $leaders = $query
            ->orderByDesc('total')
            ->limit(2)
            ->get();

        if ($leaders->isEmpty()) {
            return null;
        }

        if ($leaders->count() > 1 && (int) $leaders[0]->total === (int) $leaders[1]->total) {
            return null;
        }

        $model = $leaders[0]->owner_type;

        if (! is_a($model, Model::class, true)) {
            return null;
        }

        return $model::query()->find($leaders[0]->owner_id);
    }

    public function revoke(BadgeAssignment $assignment, ?string $reason = null): BadgeAssignment
    {
        return DB::transaction(function () use ($assignment, $reason): BadgeAssignment {
            $assignment = BadgeAssignment::query()
                ->lockForUpdate()
                ->whereKey($assignment->id)
                ->firstOrFail();

            if ($assignment->revoked_at) {
                return $assignment;
            }

            $assignment->update([
                'revoked_at' => now(),
                'revoked_reason' => $reason,
            ]);

            return $assignment;
        });
    }

    public function currentOwner(string $code): ?Model
    {
        return BadgeAssignment::query()
            ->with(['badge', 'owner'])
            ->whereHas('badge', fn (Builder $query) => $query->where('code', $code))
            ->active()
            ->latest('acquired_at')
            ->first()
            ?->owner;
    }

    public function ownedBy(Model $owner, array $filters = []): Collection|LengthAwarePaginator
    {
        $query = BadgeAssignment::query()
            ->with($filters['with'] ?? ['badge'])
            ->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->when(
                $filters['active'] ?? true,
                fn (Builder $query) => $query->active()
            )
            ->latest('acquired_at');

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }

    public function history(string $code): Collection
    {
        return BadgeAssignment::query()
            ->with(['badge', 'owner', 'awardedBy'])
            ->whereHas('badge', fn (Builder $query) => $query->where('code', $code))
            ->latest('acquired_at')
            ->get();
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Badge::query()
            ->when(
                $filters['with_count'] ?? null,
                fn (Builder $query, array|string $relations) => $query->withCount($relations)
            )
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['type'] ?? null,
                fn (Builder $query, string $type) => $query->where('type', $type)
            )
            ->when(
                $filters['source'] ?? null,
                fn (Builder $query, string $source) => $query->where('source', $source)
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

    private function ruleFromData(array $data, ?array $currentRule = null): ?array
    {
        if (array_key_exists('rule', $data)) {
            return $data['rule'];
        }

        if (($data['type'] ?? null) === Badge::TYPE_FIXED) {
            $targets = collect($data['targets'] ?? [])
                ->map(fn (string|array|null $target) => is_array($target) ? $target : $this->splitTarget($target))
                ->filter(fn (?array $target) => filled($target['type'] ?? null) && filled($target['uuid'] ?? null))
                ->unique(fn (array $target) => "{$target['type']}:{$target['uuid']}")
                ->values()
                ->all();

            if ($targets === [] && filled($data['target_type'] ?? null) && filled($data['target_uuid'] ?? null)) {
                $targets = [[
                    'type' => $data['target_type'],
                    'uuid' => $data['target_uuid'],
                ]];
            }

            return [
                'audience' => 'target',
                'targets' => $targets,
                'target_type' => $targets[0]['type'] ?? null,
                'target_uuid' => $targets[0]['uuid'] ?? null,
            ];
        }

        if (($data['type'] ?? null) === Badge::TYPE_ACHIEVEMENT) {
            return [
                'trigger' => $data['trigger'] ?? null,
                'easter_egg' => ($data['trigger'] ?? null) === 'easter_egg' ? ($data['easter_egg'] ?? null) : null,
                'threshold' => filled($data['threshold'] ?? null) ? (int) $data['threshold'] : null,
            ];
        }

        if (($data['type'] ?? null) === Badge::TYPE_SCHEDULED) {
            return [
                'trigger' => 'site.presence_window',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'daily_starts_at' => $data['daily_starts_at'] ?? null,
                'daily_ends_at' => $data['daily_ends_at'] ?? null,
            ];
        }

        if (! array_key_exists('trigger', $data)) {
            return $currentRule;
        }

        return filled($data['trigger'] ?? null) ? ['trigger' => $data['trigger']] : null;
    }

    private function splitTarget(string|array|null $target): ?array
    {
        if (! is_string($target) || ! str_contains($target, ':')) {
            return null;
        }

        [$type, $uuid] = explode(':', $target, 2);

        return compact('type', 'uuid');
    }

    private function activeBadge(string $code, string $type): Badge
    {
        $badge = Badge::query()
            ->lockForUpdate()
            ->active()
            ->where('code', $code)
            ->firstOrFail();

        if ($badge->type !== $type) {
            throw new InvalidArgumentException("Badge [{$code}] is not {$type}.");
        }

        return $badge;
    }

    private function createAssignment(Badge $badge, Model $owner, ?string $reason, array $metadata, ?Model $awardedBy): BadgeAssignment
    {
        return BadgeAssignment::create([
            'badge_id' => $badge->id,
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'awarded_by_type' => $awardedBy?->getMorphClass(),
            'awarded_by_id' => $awardedBy?->getKey(),
            'reason' => $reason,
            'metadata' => $metadata ?: null,
            'acquired_at' => now(),
        ]);
    }
}
