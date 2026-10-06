<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\OAuthAccount;
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
        $audience = $badge->rule['audience'] ?? 'all';

        if ($audience === 'all') {
            return User::query()
                ->active()
                ->get()
                ->concat(OAuthAccount::query()->get())
                ->values();
        }

        if ($audience !== 'target') {
            return collect();
        }

        $targetType = $badge->rule['target_type'] ?? null;
        $targetUuid = $badge->rule['target_uuid'] ?? null;

        $owner = match ($targetType) {
            'user' => User::query()->where('uuid', $targetUuid)->first(),
            'oauth_account' => OAuthAccount::query()->where('uuid', $targetUuid)->first(),
            default => null,
        };

        return $owner ? collect([$owner]) : collect();
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
        $audience = $badge->rule['audience'] ?? 'all';

        if ($audience === 'all') {
            return true;
        }

        if ($audience !== 'target') {
            return false;
        }

        $targetType = $badge->rule['target_type'] ?? null;
        $targetUuid = $badge->rule['target_uuid'] ?? null;

        return match ($targetType) {
            'user' => $owner instanceof User && $owner->uuid === $targetUuid,
            'oauth_account' => $owner instanceof OAuthAccount && $owner->uuid === $targetUuid,
            default => false,
        };
    }

    private function scheduledBadgeIsOpen(Badge $badge): bool
    {
        $startsAt = $badge->rule['starts_at'] ?? null;
        $endsAt = $badge->rule['ends_at'] ?? null;

        if (! $startsAt || ! $endsAt) {
            return false;
        }

        return now()->betweenIncluded($startsAt, $endsAt);
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

        if (! array_key_exists('trigger', $data)) {
            return $currentRule;
        }

        if (($data['type'] ?? null) === Badge::TYPE_FIXED) {
            return [
                'audience' => $data['audience'] ?? 'all',
                'target_type' => ($data['audience'] ?? 'all') === 'target' ? ($data['target_type'] ?? null) : null,
                'target_uuid' => ($data['audience'] ?? 'all') === 'target' ? ($data['target_uuid'] ?? null) : null,
            ];
        }

        if (($data['type'] ?? null) === Badge::TYPE_ACHIEVEMENT) {
            return [
                'trigger' => $data['trigger'] ?? null,
                'threshold' => filled($data['threshold'] ?? null) ? (int) $data['threshold'] : null,
            ];
        }

        if (($data['type'] ?? null) === Badge::TYPE_SCHEDULED) {
            return [
                'trigger' => 'site.presence_window',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'audience' => $data['audience'] ?? 'all',
                'target_type' => ($data['audience'] ?? 'all') === 'target' ? ($data['target_type'] ?? null) : null,
                'target_uuid' => ($data['audience'] ?? 'all') === 'target' ? ($data['target_uuid'] ?? null) : null,
            ];
        }

        return filled($data['trigger'] ?? null) ? ['trigger' => $data['trigger']] : null;
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
