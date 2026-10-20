<?php

namespace App\Services;

use App\Models\SongRequestBlock;
use App\Models\SongRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SongRequestBlockService
{
    public const MODULE_SONG_REQUESTS = 'song_requests';
    public const MODULE_POLLS = 'polls';
    public const MODULE_COMMENTS = 'comments';

    public function isBlocked(?Model $requester, string $module = self::MODULE_SONG_REQUESTS): bool
    {
        return $requester && SongRequestBlock::whereMorphedTo('requester', $requester)
            ->where('module', $module)
            ->exists();
    }

    public function block(Model $requester, ?User $blockedBy = null, string $module = self::MODULE_SONG_REQUESTS, ?string $reason = null): void
    {
        DB::transaction(function () use ($requester, $blockedBy, $module, $reason) {
            // The submission flow locks this same identity before checking the block.
            $requester->newQuery()->whereKey($requester->getKey())->lockForUpdate()->firstOrFail();
            SongRequestBlock::firstOrCreate([
                'requester_type' => $requester->getMorphClass(),
                'requester_id' => $requester->getKey(),
                'module' => $module,
            ], [
                'blocked_by_id' => $blockedBy?->getKey(),
                'reason' => $reason,
            ]);

            if ($module !== self::MODULE_SONG_REQUESTS) {
                return;
            }

            // Pending items have not contributed to the played-request counters.
            SongRequest::whereMorphedTo('requester', $requester)
                ->where('type', 'music')
                ->where('was_reproduced', false)
                ->where('was_canceled', false)
                ->update(['was_canceled' => true]);

            SongRequest::whereMorphedTo('requester', $requester)
                ->where('type', 'message')
                ->where('was_read', false)
                ->where('was_dismissed', false)
                ->update(['was_dismissed' => true]);
        });
    }

    public function blockModules(Model $requester, User $blockedBy, array $modules, string $reason): void
    {
        DB::transaction(function () use ($requester, $blockedBy, $modules, $reason) {
            foreach ($modules as $module) {
                $this->block($requester, $blockedBy, $module, $reason);
            }
        });
    }

    public function unblock(SongRequestBlock $block): void
    {
        DB::transaction(function () use ($block) {
            $requester = $block->requester;
            $requester?->newQuery()->whereKey($requester->getKey())->lockForUpdate()->first();
            $block->delete();
        });
    }

    public function filter(?string $module = null): Collection
    {
        return SongRequestBlock::with(['requester', 'blockedBy'])
            ->when($module, fn ($query, $module) => $query->where('module', $module))
            ->latest()
            ->get()
            ->map(fn ($block) => [
            'id' => $block->id,
            'requester_uuid' => $block->requester?->uuid,
            'name' => $block->requester?->nickname ?? $block->requester?->name ?? 'Conta removida',
            'avatar' => $block->requester?->avatar,
            'type' => $block->requester instanceof User ? 'Membro' : 'Ouvinte',
            'module' => $block->module,
            'reason' => $block->reason,
            'module_label' => match ($block->module) {
                self::MODULE_POLLS => 'Enquetes',
                self::MODULE_COMMENTS => 'Comentários',
                default => 'Pedidos musicais',
            },
            'blocked_at' => $block->created_at ? $block->created_at->format('d/m/Y').' às '.$block->created_at->format('H:i') : null,
            'blocked_by' => $block->blockedBy?->nickname ?? $block->blockedBy?->name,
        ]);
    }
}
