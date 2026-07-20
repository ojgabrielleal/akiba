<?php

namespace Tests\Unit\Actions\Locution;

use App\Actions\Locution\StartLocutionAction;
use App\Models\Onair;
use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartLocutionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_change_program_schedules_when_starting_locution(): void
    {
        $user = User::factory()->create();

        $currentProgram = Program::factory()
            ->for($user, 'host')
            ->create();

        Onair::factory()
            ->for($currentProgram, 'program')
            ->live()
            ->create();

        $liveProgram = Program::factory()
            ->withLive()
            ->for($user, 'host')
            ->create();

        $completedStartProgramSchedule = ProgramSchedule::factory()
            ->for($user)
            ->for($liveProgram)
            ->create([
                'action' => 'start_program',
                'status' => 'completed',
            ]);

        $pendingStartProgramSchedule = ProgramSchedule::factory()
            ->for($user)
            ->for($liveProgram)
            ->create([
                'action' => 'start_program',
                'status' => 'pending',
            ]);

        app(StartLocutionAction::class)->execute($user, $liveProgram, [
            'phrase' => [
                'text' => 'Starting live now',
                'icon' => '/img/icon.webp',
                'decoration' => 'default',
                'texture' => null,
            ],
            'send_notification' => false,
        ]);

        $this->assertSame('completed', $completedStartProgramSchedule->refresh()->status);
        $this->assertSame('pending', $pendingStartProgramSchedule->refresh()->status);
    }
}
