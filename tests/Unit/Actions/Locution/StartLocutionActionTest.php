<?php

namespace Tests\Unit\Actions\Locution;

use App\Actions\Locution\StartLocutionAction;
use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartLocutionActionTest extends TestCase
{
    use RefreshDatabase;

    public function testItPausesRunningStartProgramPlansWhenStartingLocution(): void
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

        $runningStartProgramPlan = Plan::factory()
            ->for($user)
            ->for($liveProgram, 'plannable')
            ->create([
                'action' => 'start_program',
                'status' => 'running',
            ]);

        $pendingStartProgramPlan = Plan::factory()
            ->for($user)
            ->for($liveProgram, 'plannable')
            ->create([
                'action' => 'start_program',
                'status' => 'pending',
            ]);

        $runningFinishProgramPlan = Plan::factory()
            ->finishProgram()
            ->for($user)
            ->for($liveProgram, 'plannable')
            ->create(['status' => 'running']);

        app(StartLocutionAction::class)->execute($user, $liveProgram, [
            'phrase' => [
                'text' => 'Starting live now',
                'icon' => '/img/icon.webp',
                'decoration' => 'default',
                'texture' => null,
            ],
        ]);

        $this->assertSame('paused', $runningStartProgramPlan->refresh()->status);
        $this->assertSame('pending', $pendingStartProgramPlan->refresh()->status);
        $this->assertSame('running', $runningFinishProgramPlan->refresh()->status);
    }
}
