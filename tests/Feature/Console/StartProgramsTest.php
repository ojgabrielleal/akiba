<?php

namespace Tests\Feature\Console;

use App\Models\Onair;
use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StartProgramsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_live_locution_expires_due_schedule(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $user = User::factory()->create();
        $scheduledProgram = Program::factory()->withScheduled()->for($user, 'host')->create();
        $liveProgram = Program::factory()->withLive()->for($user, 'host')->create();

        $schedule = ProgramSchedule::factory()
            ->for($user)
            ->for($scheduledProgram)
            ->create(['scheduled_at' => now()->subMinutes(2)]);

        Onair::factory()
            ->for($liveProgram, 'program')
            ->live()
            ->create();

        $this->artisan('programs:start')
            ->expectsOutput('Expired program schedules: 1.')
            ->expectsOutput('Scheduled programs skipped because a live locution is on air.')
            ->assertSuccessful();

        $this->assertSame('expired', $schedule->refresh()->status);
        $this->assertFalse(Onair::live()->whereBelongsTo($scheduledProgram)->exists());
    }

    public function test_live_locution_keeps_future_schedule_pending(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $user = User::factory()->create();
        $scheduledProgram = Program::factory()->withScheduled()->for($user, 'host')->create();
        $liveProgram = Program::factory()->withLive()->for($user, 'host')->create();

        $schedule = ProgramSchedule::factory()
            ->for($user)
            ->for($scheduledProgram)
            ->create(['scheduled_at' => now()->addMinute()]);

        Onair::factory()
            ->for($liveProgram, 'program')
            ->live()
            ->create();

        $this->artisan('programs:start')->assertSuccessful();

        $this->assertSame('pending', $schedule->refresh()->status);
    }

    public function test_due_schedule_starts_when_there_is_no_live_locution(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $user = User::factory()->create();
        $previousProgram = Program::factory()->withScheduled()->for($user, 'host')->create();
        $program = Program::factory()->withScheduled()->for($user, 'host')->create();

        Onair::factory()
            ->for($previousProgram, 'program')
            ->scheduled()
            ->create();

        $schedule = ProgramSchedule::factory()
            ->for($user)
            ->for($program)
            ->create(['scheduled_at' => now()->subMinutes(2)]);

        $this->artisan('programs:start')->assertSuccessful();

        $this->assertSame('completed', $schedule->refresh()->status);
        $this->assertFalse(Onair::live()->whereBelongsTo($previousProgram)->exists());
        $this->assertTrue(Onair::live()->whereBelongsTo($program)->exists());
    }
}
