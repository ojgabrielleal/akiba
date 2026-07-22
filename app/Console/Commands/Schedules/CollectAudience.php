<?php

namespace App\Console\Commands\Schedules;

use App\Services\Process\AudienceCollectorService;
use Illuminate\Console\Command;

class CollectAudience extends Command
{
    protected $signature = 'audience:collect';
    protected $description = 'Collect and store the current audience of active radio stations.';

    public function handle(AudienceCollectorService $audienceCollectorService): int
    {
        $audienceCollectorService->collect();
        $this->info('Radio audience collected successfully.');
        return self::SUCCESS;
    }
}
