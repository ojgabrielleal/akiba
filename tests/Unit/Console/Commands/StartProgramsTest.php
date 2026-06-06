<?php

namespace Tests\Unit\Console\Commands;

use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function testItStartsDefaultAutoDjWhenThereAreNoDuePlansAndNothingIsOnAir(): void
    {
        $user = User::factory()->create();

        Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->create();

        $defaultAutoDj = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        $this->artisan('programs:start')
            ->assertSuccessful();

        $onair = Onair::live()->first();

        $this->assertTrue($onair->program->is($defaultAutoDj));
        $this->assertSame('auto_dj', $onair->execution_mode);
        $this->assertFalse($onair->allows_song_requests);
    }

    public function testItDoesNotStartDefaultAutoDjWhenSomethingIsAlreadyOnAir(): void
    {
        $user = User::factory()->create();

        $liveProgram = Program::factory()
            ->for($user, 'host')
            ->withLive()
            ->create();

        Onair::factory()
            ->for($liveProgram, 'program')
            ->live()
            ->create();

        Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        $this->artisan('programs:start')
            ->assertSuccessful();

        $this->assertSame(1, Onair::live()->count());
        $this->assertTrue(Onair::live()->first()->program->is($liveProgram));
    }

    public function testItStartsDuePlanInsteadOfDefaultAutoDj(): void
    {
        $user = User::factory()->create();

        $currentProgram = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        Onair::factory()
            ->for($currentProgram, 'program')
            ->autoDj()
            ->create();

        $scheduledProgram = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        $plan = Plan::factory()
            ->for($user)
            ->for($scheduledProgram, 'plannable')
            ->create([
                'scheduled_at' => now()->subMinute(),
                'status' => 'pending',
            ]);

        $this->artisan('programs:start')
            ->assertSuccessful();

        $plan->refresh();
        $onair = Onair::live()->first();

        $this->assertSame('running', $plan->status);
        $this->assertTrue($onair->program->is($scheduledProgram));
        $this->assertSame('scheduled', $onair->execution_mode);
    }

    public function testItStartsDefaultAutoDjWhenScheduledProgramFinishesAndThereIsNoNextDueProgram(): void
    {
        $user = User::factory()->create();

        $scheduledProgram = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        Onair::factory()
            ->for($scheduledProgram, 'program')
            ->scheduled()
            ->create();

        $startPlan = Plan::factory()
            ->for($user)
            ->for($scheduledProgram, 'plannable')
            ->create([
                'action' => 'start_program',
                'scheduled_at' => now()->subHours(2),
                'status' => 'running',
            ]);

        $finishPlan = Plan::factory()
            ->finishProgram()
            ->for($user)
            ->for($scheduledProgram, 'plannable')
            ->create([
                'scheduled_at' => now()->subMinute(),
                'status' => 'pending',
            ]);

        $defaultAutoDj = Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        $this->artisan('programs:start')
            ->assertSuccessful();

        $onair = Onair::live()->first();

        $this->assertSame('completed', $startPlan->refresh()->status);
        $this->assertSame('completed', $finishPlan->refresh()->status);
        $this->assertTrue($onair->program->is($defaultAutoDj));
        $this->assertSame('auto_dj', $onair->execution_mode);
    }

    public function testItDoesNotStartDefaultAutoDjBetweenFinishAndNextDueProgram(): void
    {
        $user = User::factory()->create();

        $finishedProgram = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        Onair::factory()
            ->for($finishedProgram, 'program')
            ->scheduled()
            ->create();

        Plan::factory()
            ->finishProgram()
            ->for($user)
            ->for($finishedProgram, 'plannable')
            ->create([
                'scheduled_at' => now()->subMinutes(2),
                'status' => 'pending',
            ]);

        $nextProgram = Program::factory()
            ->for($user, 'host')
            ->withScheduled()
            ->create();

        Plan::factory()
            ->for($user)
            ->for($nextProgram, 'plannable')
            ->create([
                'action' => 'start_program',
                'scheduled_at' => now()->subMinute(),
                'status' => 'pending',
            ]);

        Program::factory()
            ->for($user, 'host')
            ->withAutoDJ()
            ->asDefault()
            ->create();

        $this->artisan('programs:start')
            ->assertSuccessful();

        $onair = Onair::live()->first();

        $this->assertTrue($onair->program->is($nextProgram));
        $this->assertSame('scheduled', $onair->execution_mode);
        $this->assertSame(1, Onair::live()->count());
    }
}
