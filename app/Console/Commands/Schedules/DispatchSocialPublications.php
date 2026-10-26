<?php

namespace App\Console\Commands\Schedules;

use App\Jobs\PublishSocialPublicationSchedule;
use App\Models\SocialPublicationSchedule;
use Illuminate\Console\Command;

class DispatchSocialPublications extends Command
{
    protected $signature = 'social-publications:dispatch-pending {--limit=100 : Maximum schedules to dispatch per run}';

    protected $description = 'Dispatch due social publication schedules to the Laravel queue.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $schedules = SocialPublicationSchedule::query()
            ->pending()
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $schedules->each(
            fn (SocialPublicationSchedule $schedule) => PublishSocialPublicationSchedule::dispatch($schedule->id),
        );

        $this->info("Social publication schedules dispatched: {$schedules->count()}.");

        return self::SUCCESS;
    }
}
