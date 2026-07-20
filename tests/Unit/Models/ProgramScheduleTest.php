<?php

namespace Tests\Unit\Models;

use App\Models\Program;
use App\Models\ProgramSchedule;

use App\Models\User;
use Database\Seeders\ProgramScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from ProgramSchedule model relationships.
     */
    public function test_user_relationship(): void
    {
        $user = User::factory()->create();

        $schedule = ProgramSchedule::factory()
            ->for($user)
            ->create();

        $this->assertTrue($schedule->user->is($user));
    }

    public function test_program_relationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $schedule = ProgramSchedule::factory()
            ->for($user)
            ->for($program)
            ->create();

        $this->assertTrue($schedule->program->is($program));
    }

    public function test_seeder_creates_future_program_schedules(): void
    {
        $user = User::factory()->create([
            'is_virtual' => false,
        ]);

        Program::factory()
            ->withLive()
            ->for($user, 'host')
            ->create();

        Program::factory()
            ->withScheduled()
            ->for($user, 'host')
            ->create();

        Program::factory()
            ->withPlaylist()
            ->for($user, 'host')
            ->create();

        Program::factory()
            ->withAutoDJ()
            ->for($user, 'host')
            ->create();

        $this->seed(ProgramScheduleSeeder::class);

        $schedules = ProgramSchedule::all();

        $this->assertCount(2, $schedules);
        $this->assertTrue($schedules->every(fn (ProgramSchedule $schedule) => $schedule->scheduled_at->isFuture()));
        $this->assertCount(2, $schedules->where('action', 'start_program'));
    }

    public function test_unexecuted_scope(): void
    {
        $pending = ProgramSchedule::factory()->create(['status' => 'pending']);
        $completed = ProgramSchedule::factory()->create(['status' => 'completed']);
        $cancelled = ProgramSchedule::factory()->create(['status' => 'cancelled']);
        $failed = ProgramSchedule::factory()->create(['status' => 'failed']);
        $expired = ProgramSchedule::factory()->create(['status' => 'expired']);

        $schedules = ProgramSchedule::pendingExecution()->get();

        $this->assertTrue($schedules->contains($pending));
        $this->assertFalse($schedules->contains($completed));
        $this->assertFalse($schedules->contains($cancelled));
        $this->assertFalse($schedules->contains($failed));
        $this->assertFalse($schedules->contains($expired));
    }
}
