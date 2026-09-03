<?php

namespace App\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PruneCache extends Command
{
    protected $signature = 'cache:prune-akiba';

    protected $description = 'Clear the application cache as monthly file cache hygiene.';

    public function handle(): int
    {
        Cache::clear();

        $this->info('Application cache cleared successfully.');

        return self::SUCCESS;
    }
}
