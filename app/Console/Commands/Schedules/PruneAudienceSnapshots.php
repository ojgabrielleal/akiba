<?php

namespace App\Console\Commands\Schedules;

use App\Models\RadioAudienceSnapshot;
use Illuminate\Console\Command;

class PruneAudienceSnapshots extends Command
{
    protected $signature = 'audience:prune';

    protected $description = 'Delete radio audience snapshots older than six months.';

    public function handle(): int
    {
        $cutoff = now()->subMonths(6);
        $deleted = RadioAudienceSnapshot::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Audience snapshots deleted: {$deleted}.");

        return self::SUCCESS;
    }
}
