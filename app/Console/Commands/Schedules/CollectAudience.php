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
        $summary = $audienceCollectorProcess->collect();

        $this->info(sprintf(
            'Radio audience collected successfully. Stations: %d, online: %d, offline: %d, invalid: %d, slow: %d.',
            $summary['stations'],
            $summary['online'],
            $summary['offline'],
            $summary['invalid_response'],
            $summary['slow'],
        ));

        return self::SUCCESS;
    }
}
