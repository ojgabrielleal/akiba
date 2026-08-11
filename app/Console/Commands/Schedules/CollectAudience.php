<?php

namespace App\Console\Commands\Schedules;

use App\Processing\AudienceCollectorProcess;
use Illuminate\Console\Command;

class CollectAudience extends Command
{
    protected $signature = 'audience:collect';
    protected $description = 'Collect and store the current audience of active radio stations.';

    public function handle(AudienceCollectorProcess $audienceCollectorProcess): int
    {
        $audienceCollectorProcess->collect();

        $this->info('Radio audience collected successfully.');

        return self::SUCCESS;
    }
}
