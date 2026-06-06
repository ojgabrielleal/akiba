<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Task model relationships.
     */
    public function testResponsibleRelationship(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()
            ->for($user, 'responsible')
            ->create();

        $this->assertTrue($task->responsible->is($user));
    }

    /**
     * Tests from Task model scopes.
     */
    public function testActiveScope(): void
    {
        $user = User::factory()->create();

        $activeTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'is_active' => true
            ]);

        $inactiveTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'is_active' => false
            ]);

        $activeTasks = Task::active()->get();

        $this->assertTrue($activeTasks->contains($activeTask));
        $this->assertFalse($activeTasks->contains($inactiveTask));
    }

    public function testIncompletedScope(): void
    {
        $user = User::factory()->create();

        $inProgressTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'status' => 'pending'
            ]);

        $inReviewTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'status' => 'in_review'
            ]);

        $overdueTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'status' => 'pending',
                'dead_line' => now()->subDay()->toDateString()
            ]);

        $completedTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'status' => 'completed'
            ]);

        $incompletedTasks = Task::incompleted()->get();

        $this->assertTrue($incompletedTasks->contains($inProgressTask));
        $this->assertTrue($incompletedTasks->contains($inReviewTask));
        $this->assertTrue($incompletedTasks->contains($overdueTask));
        $this->assertFalse($incompletedTasks->contains($completedTask));
    }

    public function testMineScope(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myTask = Task::factory()
            ->for($user, 'responsible')
            ->create();

        $otherTask = Task::factory()
            ->for($otherUser, 'responsible')
            ->create();

        $this->actingAs($user);

        $myTasks = Task::mine()->get();

        $this->assertTrue($myTasks->contains($myTask));
        $this->assertFalse($myTasks->contains($otherTask));
    }

    /**
     * Tests from Task model attributes.
     */
    public function testDaysRemainingAttribute(): void
    {
        $today = Carbon::parse('2026-01-20');

        Carbon::setTestNow($today);

        $user = User::factory()->create();

        $overdueTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-15',
                'status' => 'pending'
            ]);

        $futureTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-30',
                'status' => 'pending'
            ]);

        $completedTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-15',
                'status' => 'completed'
            ]);

        $this->assertSame(0, $overdueTask->days_remaining);
        $this->assertSame(10, $futureTask->days_remaining);
        $this->assertSame(0, $completedTask->days_remaining);

        Carbon::setTestNow();
    }

    public function testIsOverdueAttribute(): void
    {
        $today = Carbon::parse('2026-01-20');

        Carbon::setTestNow($today);

        $user = User::factory()->create();

        $overdueTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-15',
                'status' => 'pending'
            ]);

        $todayTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-20',
                'status' => 'pending'
            ]);

        $futureTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-25',
                'status' => 'pending'
            ]);

        $completedOverdueTask = Task::factory()
            ->for($user, 'responsible')
            ->create([
                'dead_line' => '2026-01-15',
                'status' => 'completed'
            ]);

        $this->assertTrue($overdueTask->is_overdue);
        $this->assertFalse($todayTask->is_overdue);
        $this->assertFalse($futureTask->is_overdue);
        $this->assertFalse($completedOverdueTask->is_overdue);

        Carbon::setTestNow();
    }
}
