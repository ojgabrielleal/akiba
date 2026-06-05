<?php

namespace App\Console\Commands\Schedules;

use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartPrograms extends Command
{
    protected $signature = 'programs:start';

    protected $description = 'Start scheduled programs that are ready to go on air.';

    public function handle(): int
    {
        $processed = 0;
        $failed = 0;

        if ($this->hasPausedProgramPlan()) {
            $this->info('Scheduled programs skipped because a program plan is paused.');

            return self::SUCCESS;
        }

        Plan::query()
            ->where('action', 'start_program')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get()
            ->each(function (Plan $plan) use (&$processed, &$failed) {
                try {
                    DB::transaction(function () use ($plan, &$processed) {
                        $program = $plan->plannable;

                        if (! $program instanceof Program) {
                            $plan->update(['status' => 'failed']);

                            return;
                        }

                        Onair::live()->update([
                            'in_air' => false,
                            'allows_song_requests' => false,
                        ]);

                        $program->onair()->create([
                            'execution_mode' => $program->execution_mode,
                            'phrase' => $this->selectPhrase($program),
                            'allows_song_requests' => false,
                        ]);

                        $plan->update(['status' => 'running']);

                        $processed++;
                    });
                } catch (Throwable $exception) {
                    Plan::query()
                        ->whereKey($plan->id)
                        ->where('status', 'pending')
                        ->update(['status' => 'failed']);

                    $failed++;
                    report($exception);
                }
            });

        if ($failed > 0) {
            $this->warn("Scheduled programs failed: {$failed}.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function selectPhrase(Program $program): array
    {
        if (empty($program->phrases)) {
            return [];
        }

        return collect($program->phrases)->random();
    }

    private function hasPausedProgramPlan(): bool
    {
        return Plan::query()
            ->where('action', 'start_program')
            ->where('status', 'paused')
            ->exists();
    }
}
