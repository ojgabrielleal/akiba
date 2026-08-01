<?php

namespace App\Actions\Program;

use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;

use App\Services\Process\ImageProcessService;

use DomainException;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreProgramAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, User $responsible, array $data, ?UploadedFile $image = null): Program
    {
        return DB::transaction(function () use ($user, $responsible, $data, $image) {
            $program = $this->storeProgram($responsible, $data, $image);

            if ($program->is_default_auto_dj) {
                $this->clearOtherDefaultAutoDjPrograms($program);
            }

            $this->storeAirtimes($program, $data);
            $this->storeSchedules($program, $user, $data);

            return $program;
        });
    }

    private function storeProgram(User $responsible, array $data, ?UploadedFile $image = null): Program
    {
        return Program::create([
            'user_id' => $responsible->id,
            'name' => $data['name'],
            'image' => $this->image->store('programs', $image),
            'access_type' => $data['execution_mode'] === 'auto_dj' ? null : $data['access_type'],
            'execution_mode' => $data['execution_mode'],
            'is_default_auto_dj' => filter_var($data['is_default_auto_dj'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'phrases' => $data['phrases'] ?? [],
        ]);
    }

    private function storeAirtimes(Program $program, array $data): void
    {
        if (! empty($data['airtimes']) && $data['execution_mode'] === 'live') {
            $program->programAirtimes()->createMany(collect($data['airtimes']));
        }
    }

    private function storeSchedules(Program $program, User $user, array $data): void
    {
        if (empty($data['schedules']) || $data['execution_mode'] === 'live') {
            return;
        }

        $schedules = collect($data['schedules']);
        $this->ensureSchedulesCanBeScheduled($schedules);

        foreach ($schedules as $schedule) {
            $program->schedules()->create([
                'user_id' => $user->id,
                'action' => 'start_program',
                'scheduled_at' => $schedule['scheduled_at'],
            ]);
        }
    }

    private function clearOtherDefaultAutoDjPrograms(Program $program): void
    {
        Program::where('id', '!=', $program->id)
            ->where('is_default_auto_dj', true)
            ->update(['is_default_auto_dj' => false]);
    }

    private function ensureSchedulesCanBeScheduled($schedules): void
    {
        $scheduledTimes = $schedules
            ->pluck('scheduled_at')
            ->filter()
            ->values();

        if ($scheduledTimes->duplicates()->isNotEmpty()) {
            throw new DomainException('Existe agendamento duplicados, verifique os horários informados.');
        }

        if ($scheduledTimes->isEmpty()) {
            return;
        }

        $hasConflict = ProgramSchedule::pendingExecution()
            ->where('action', 'start_program')
            ->whereIn('scheduled_at', $scheduledTimes->all())
            ->exists();

        if ($hasConflict) {
            throw new DomainException('Este horário já está ocupado por outro agendamento');
        }
    }
}
