<?php

namespace App\Actions\Radio\Program;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Services\Process\ImageProcessService;

use App\Models\Plan;
use App\Models\User;
use App\Models\Program;
use DomainException;

class CreateProgramAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data, ?UploadedFile $image = null): Program
    {
        return DB::transaction(function () use ($user, $data, $image) {
            $program = Program::create([
                'user_id' => $data['access_type'] === 'free' ? $user->id : User::where('uuid', $data['user'])->first()->id,
                'name' => $data['name'],
                'image' => $this->image->store('programs', $image),
                'access_type' => $data['access_type'],
                'execution_mode' => $data['execution_mode'],
                'is_default_auto_dj' => filter_var($data['is_default_auto_dj'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'phrases' => $data['phrases'],
            ]);

            if ($program->is_default_auto_dj) {
                $this->clearOtherDefaultAutoDjPrograms($program);
            }

            if (!empty($data['airtimes']) && $data['execution_mode'] === 'live') {
                $program->programAirtimes()->createMany(collect($data['airtimes']));
            }

            if (!empty($data['plans']) && $data['execution_mode'] !== 'live') {
                $plans = collect($data['plans']);
                $this->ensurePlansCanBeScheduled($plans);

                foreach ($plans as $plan) {
                    $program->plans()->create([
                        'user_id' => $user->id,
                        'action' => 'start_program',
                        'scheduled_at' => $plan['scheduled_at'],
                    ]);
                }
            }

            return $program;
        });
    }


    private function clearOtherDefaultAutoDjPrograms(Program $program): void
    {
        Program::where('id', '!=', $program->id)
            ->where('is_default_auto_dj', true)
            ->update(['is_default_auto_dj' => false]);
    }

    private function ensurePlansCanBeScheduled($plans): void
    {
        $scheduledTimes = $plans
            ->pluck('scheduled_at')
            ->filter()
            ->values();

        if ($scheduledTimes->duplicates()->isNotEmpty()) {
            throw new DomainException('Existe agendamento duplicados, verifique os horários informados.');
        }

        if ($scheduledTimes->isEmpty()) {
            return;
        }

        $hasConflict = Plan::unexecuted()
            ->where('action', 'start_program')
            ->whereIn('scheduled_at', $scheduledTimes->all())
            ->exists();

        if ($hasConflict) {
            throw new DomainException('Este horário já está ocupado por outro agendamento');
        }
    }
}
