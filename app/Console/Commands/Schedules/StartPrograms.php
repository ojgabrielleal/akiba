<?php

namespace App\Console\Commands\Schedules;

use App\Models\Onair;
use App\Models\Program;
use App\Models\ProgramSchedule;
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

        if ($this->hasLiveLocution()) {
            $expired = $this->expireDueSchedules();

            if ($expired > 0) {
                $this->warn("Expired program schedules: {$expired}.");
            }

            $this->info('Scheduled programs skipped because a live locution is on air.');
            return self::SUCCESS;
        }

        $schedules = ProgramSchedule::query()
            ->where('action', 'start_program')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        if ($schedules->isEmpty()) {
            $this->startDefaultAutoDjWhenIdle();
            return self::SUCCESS;
        }

        $schedules->each(function (ProgramSchedule $schedule) use (&$processed, &$failed) {
            try {
                DB::transaction(function () use ($schedule, &$processed) {
                    $program = $schedule->program;

                    if (!$program instanceof Program) {
                        $schedule->update(['status' => 'failed']);
                        return;
                    }

                    $this->startProgram($program, $schedule);
                    $processed++;
                });
            } catch (Throwable $exception) {
                ProgramSchedule::query()
                    ->whereKey($schedule->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'failed']);

                $failed++;
                report($exception);
            }
        });

        $this->startDefaultAutoDjWhenIdle();

        $failed > 0 ?? $this->warn("Scheduled programs failed: {$failed}.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function selectPhrase(Program $program): array
    {
        if (empty($program->phrases)) {
            return [];
        }

        return collect($program->phrases)->random();
    }

    private function expireDueSchedules(): int
    {
        return ProgramSchedule::query()
            ->where('action', 'start_program')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    private function hasLiveLocution(): bool
    {
        return Onair::live()
            ->where('execution_mode', 'live')
            ->exists();
    }

    private function startProgram(Program $program, ProgramSchedule $schedule): void
    {
        Onair::live()->update([
            'in_air' => false,
            'allows_song_requests' => false,
        ]);

        $schedule->update(['status' => 'completed']);

        $program->onair()->create([
            'execution_mode' => $program->execution_mode,
            'phrase' => $this->selectPhrase($program),
            'allows_song_requests' => false,
        ]);
    }

    private function startDefaultAutoDjWhenIdle(): void
    {
        if(Onair::live()->exists()) return;

        $autoDj = Program::query()
            ->where('execution_mode', 'auto_dj')
            ->where('is_default_auto_dj', true)
            ->first();

        if(!$autoDj) return;

        $autoDj->onair()->create([
            'execution_mode' => 'auto_dj',
            'phrase' => $this->selectPhrase($autoDj),
            'allows_song_requests' => false,
        ]);
    }
}
