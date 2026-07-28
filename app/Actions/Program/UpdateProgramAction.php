<?php

namespace App\Actions\Program;

use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;

use App\Services\Process\ImageProcessService;

use DomainException;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateProgramAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Program $program, User $user, User $responsible, array $data, ?UploadedFile $image = null): Program
    {
        return DB::transaction(function () use ($program, $user, $responsible, $data, $image) {
            $this->updateProgram($program, $responsible, $data, $image);

            if ($program->is_default_auto_dj) {
                $this->clearOtherDefaultAutoDjPrograms($program);
            }

            $this->clearUnavailableExecutionData($program);
            $this->syncExecutionData($program, $user, $data);

            return $program;
        });
    }

    private function updateProgram(Program $program, User $responsible, array $data, ?UploadedFile $image = null): void
    {
        $program->fill([
            'user_id' => $responsible->id,
            'name' => $data['name'],
            'image' => $this->image->store('programs', $image, $program->image),
            'access_type' => $data['access_type'],
            'execution_mode' => $data['execution_mode'],
            'is_default_auto_dj' => filter_var($data['is_default_auto_dj'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'phrases' => $data['phrases'] ?? [],
        ]);

        if ($program->isDirty()) {
            $program->save();
        }
    }

    private function clearUnavailableExecutionData(Program $program): void
    {
        if ($program->execution_mode === 'live') {
            $program->schedules()->where('action', 'start_program')->delete();

            return;
        }

        $program->programAirtimes()->delete();
    }

    private function syncExecutionData(Program $program, User $user, array $data): void
    {
        if ($program->execution_mode === 'live') {
            $this->syncAirtimes($program, $data);

            return;
        }

        $this->syncSchedules($program, $user, $data);
    }

    private function syncAirtimes(Program $program, array $data): void
    {
        $airtimes = collect($data['airtimes'] ?? []);

        $uuids = $airtimes->pluck('uuid')->filter()->toArray();
        $program->programAirtimes()->whereNotIn('uuid', $uuids)->delete();

        foreach ($airtimes as $schedule) {
            $program->programAirtimes()->updateOrCreate(
                ['uuid' => $schedule['uuid'] ?? null],
                [
                    'day' => $schedule['day'],
                    'hour' => $schedule['hour'],
                ]
            );
        }
    }

    private function syncSchedules(Program $program, User $user, array $data): void
    {
        $schedules = collect($data['schedules'] ?? []);
        $uuids = $schedules->pluck('uuid')->filter()->toArray();

        $this->ensureSchedulesCanBeScheduled($schedules, $uuids);

        $program->schedules()
            ->where('action', 'start_program')
            ->whereNotIn('uuid', $uuids)
            ->delete();

        foreach ($schedules as $schedule) {
            $program->schedules()->updateOrCreate(
                ['uuid' => $schedule['uuid'] ?? null],
                [
                    'user_id' => $user->id,
                    'action' => 'start_program',
                    'scheduled_at' => $schedule['scheduled_at'],
                ]
            );
        }
    }

    private function clearOtherDefaultAutoDjPrograms(Program $program): void
    {
        Program::where('id', '!=', $program->id)
            ->where('is_default_auto_dj', true)
            ->update(['is_default_auto_dj' => false]);
    }

    private function ensureSchedulesCanBeScheduled($schedules, array $ignoredUuids = []): void
    {
        $scheduledTimes = $schedules
            ->pluck('scheduled_at')
            ->filter()
            ->values();

        if ($scheduledTimes->duplicates()->isNotEmpty()) {
            throw new DomainException('Este horário foi informado mais de uma vez.');
        }

        if ($scheduledTimes->isEmpty()) {
            return;
        }

        $hasConflict = ProgramSchedule::pendingExecution()
            ->where('action', 'start_program')
            ->whereIn('scheduled_at', $scheduledTimes->all())
            ->when($ignoredUuids, fn ($query) => $query->whereNotIn('uuid', $ignoredUuids))
            ->exists();

        if ($hasConflict) {
            throw new DomainException('Este horário já está ocupado por outro agendamento.');
        }
    }
}
