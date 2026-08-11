<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Support\Facades\DB;
use App\Models\ProgramSchedule;
use App\Models\User;
use App\Processing\ImageProcess;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProgramService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function deactivate(Program $program): Program
    {
        return DB::transaction(function () use ($program) {
            $program->update(['is_active' => false]);

            return $program;
        });
    }

    public function store(User $user, User $responsible, array $data, ?UploadedFile $image = null): Program
    {
        return DB::transaction(function () use ($user, $responsible, $data, $image) {
            $program = $this->storeStoreProgram($responsible, $data, $image);

            if ($program->is_default_auto_dj) {
                $this->storeClearOtherDefaultAutoDjPrograms($program);
            }

            $this->storeStoreAirtimes($program, $data);
            $this->storeStoreSchedules($program, $user, $data);

            return $program;
        });
    }

    private function storeStoreProgram(User $responsible, array $data, ?UploadedFile $image = null): Program
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

    private function storeStoreAirtimes(Program $program, array $data): void
    {
        if (! empty($data['airtimes']) && $data['execution_mode'] === 'live') {
            $program->programAirtimes()->createMany(collect($data['airtimes']));
        }
    }

    private function storeStoreSchedules(Program $program, User $user, array $data): void
    {
        if (empty($data['schedules']) || $data['execution_mode'] === 'live') {
            return;
        }

        $schedules = collect($data['schedules']);
        $this->storeEnsureSchedulesCanBeScheduled($schedules);

        foreach ($schedules as $schedule) {
            $program->schedules()->create([
                'user_id' => $user->id,
                'action' => 'start_program',
                'scheduled_at' => $schedule['scheduled_at'],
            ]);
        }
    }

    private function storeClearOtherDefaultAutoDjPrograms(Program $program): void
    {
        Program::where('id', '!=', $program->id)
            ->where('is_default_auto_dj', true)
            ->update(['is_default_auto_dj' => false]);
    }

    private function storeEnsureSchedulesCanBeScheduled($schedules): void
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

    public function update(Program $program, User $user, User $responsible, array $data, ?UploadedFile $image = null): Program
    {
        return DB::transaction(function () use ($program, $user, $responsible, $data, $image) {
            $this->updateUpdateProgram($program, $responsible, $data, $image);

            if ($program->is_default_auto_dj) {
                $this->updateClearOtherDefaultAutoDjPrograms($program);
            }

            $this->updateClearUnavailableExecutionData($program);
            $this->updateSyncExecutionData($program, $user, $data);

            return $program;
        });
    }

    private function updateUpdateProgram(Program $program, User $responsible, array $data, ?UploadedFile $image = null): void
    {
        $program->fill([
            'user_id' => $responsible->id,
            'name' => $data['name'],
            'image' => $this->image->store('programs', $image, $program->image),
            'access_type' => $data['execution_mode'] === 'auto_dj' ? null : $data['access_type'],
            'execution_mode' => $data['execution_mode'],
            'is_default_auto_dj' => filter_var($data['is_default_auto_dj'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'phrases' => $data['phrases'] ?? [],
        ]);

        if ($program->isDirty()) {
            $program->save();
        }
    }

    private function updateClearUnavailableExecutionData(Program $program): void
    {
        if ($program->execution_mode === 'live') {
            $program->schedules()->where('action', 'start_program')->delete();

            return;
        }

        $program->programAirtimes()->delete();
    }

    private function updateSyncExecutionData(Program $program, User $user, array $data): void
    {
        if ($program->execution_mode === 'live') {
            $this->updateSyncAirtimes($program, $data);

            return;
        }

        $this->updateSyncSchedules($program, $user, $data);
    }

    private function updateSyncAirtimes(Program $program, array $data): void
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

    private function updateSyncSchedules(Program $program, User $user, array $data): void
    {
        $schedules = collect($data['schedules'] ?? []);
        $uuids = $schedules->pluck('uuid')->filter()->toArray();

        $this->updateEnsureSchedulesCanBeScheduled($schedules, $uuids);

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

    private function updateClearOtherDefaultAutoDjPrograms(Program $program): void
    {
        Program::where('id', '!=', $program->id)
            ->where('is_default_auto_dj', true)
            ->update(['is_default_auto_dj' => false]);
    }

    private function updateEnsureSchedulesCanBeScheduled($schedules, array $ignoredUuids = []): void
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

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Program::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                $filters['available_for_locution'] ?? null,
                fn (Builder $query, $user) => $query->availableForLocution($user)
            )
            ->when(
                $filters['execution_mode'] ?? null,
                fn (Builder $query, string $executionMode) => $query->where('execution_mode', $executionMode)
            )
            ->when(
                $filters['public_schedule'] ?? false,
                fn (Builder $query) => $query->where(function (Builder $query) {
                    $query->where('access_type', 'private')
                        ->whereHas('programAirtimes');
                })
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->whereLike('name', '%'.trim($search).'%')
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage)->withQueryString(),
            fn (Builder $query) => $query->get()
        );
    }}
