<?php

namespace App\Actions\Program;

use Illuminate\Http\UploadedFile;
use App\Services\Process\ImageProcessService;

use App\Models\Plan;
use App\Models\User;
use App\Models\Program;
use DomainException;

class UpdateProgramAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Program $program, User $user, array $data, ?UploadedFile $image = null): Program
    {
        return $program->getConnection()->transaction(function () use ($program, $user, $data, $image) {
            $program->fill([
                'user_id' => $data['access_type'] === 'free' ? $user->id : User::where('uuid', $data['user'])->first()->id,
                'name' => $data['name'],
                'image' => $this->image->store('programs', $image, $program->image),
                'access_type' => $data['access_type'],
                'execution_mode' => $data['execution_mode'],
                'is_default_auto_dj' => filter_var($data['is_default_auto_dj'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'phrases' => $data['phrases'] ?? [],
            ]);

            if ($program->isDirty()) $program->save();

            if ($program->is_default_auto_dj) {
                $this->clearOtherDefaultAutoDjPrograms($program);
            }

            if ($program->execution_mode === 'live') {
                $program->plans()->where('action', 'start_program')->delete();
            } else {
                $program->programAirtimes()->delete();
            }

            if ($program->execution_mode === 'live') {
                $airtimes = collect($data['airtimes'] ?? []);

                $uuids = $airtimes->pluck('uuid')->filter()->toArray();
                $program->programAirtimes()->whereNotIn('uuid', $uuids)->delete();

                foreach ($airtimes as $schedule) {
                    $program->programAirtimes()->updateOrCreate(
                        ['uuid' => $schedule['uuid']],
                        [
                            'day' => $schedule['day'], 
                            'hour' => $schedule['hour']
                        ]
                    );
                }
            }else{
                $plans = collect($data['plans']);
                $uuids = $plans->pluck('uuid')->filter()->toArray();

                $this->ensurePlansCanBeScheduled($plans, $uuids);

                $program->plans()
                    ->where('action', 'start_program')
                    ->whereNotIn('uuid', $uuids)
                    ->delete();

                foreach ($plans as $plan) {
                    $program->plans()->updateOrCreate(
                        ['uuid' => $plan['uuid']],
                        [
                            'user_id' => $user->id,
                            'action' => 'start_program',
                            'scheduled_at' => $plan['scheduled_at'],
                        ]
                    );
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

    private function ensurePlansCanBeScheduled($plans, array $ignoredUuids = []): void
    {
        $scheduledTimes = $plans
            ->pluck('scheduled_at')
            ->filter()
            ->values();

        if ($scheduledTimes->duplicates()->isNotEmpty()) {
            throw new DomainException('Este horário foi informado mais de uma vez.');
        }

        if ($scheduledTimes->isEmpty()) {
            return;
        }

        $hasConflict = Plan::pendingExecution()
            ->where('action', 'start_program')
            ->whereIn('scheduled_at', $scheduledTimes->all())
            ->when($ignoredUuids, fn ($query) => $query->whereNotIn('uuid', $ignoredUuids))
            ->exists();

        if ($hasConflict) {
            throw new DomainException('Este horário já está ocupado por outro agendamento.');
        }
    }
}
