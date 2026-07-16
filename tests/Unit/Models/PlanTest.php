<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Plan;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\PlanSeeder;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Plan model relationships.
     */
    public function testUserRelationship(): void
    {
        $user = User::factory()->create();

        $plan = Plan::factory()
            ->for($user)
            ->create();

        $this->assertTrue($plan->user->is($user));
    }

    public function testPlannableRelationship(): void
    {
        $user = User::factory()->create();

        $program = Program::factory()
            ->for($user, 'host')
            ->create();

        $plan = Plan::factory()
            ->for($user)
            ->create([
                'plannable_type' => Program::class,
                'plannable_id' => $program->id,
            ]);

        $this->assertTrue($plan->plannable->is($program));
    }

    public function testPlanSeederCreatesFutureProgramPlans(): void
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

        $this->seed(PlanSeeder::class);

        $plans = Plan::all();

        $this->assertCount(4, $plans);
        $this->assertTrue($plans->every(fn (Plan $plan) => $plan->scheduled_at->isFuture()));
        $this->assertCount(2, $plans->where('action', 'start_program'));
        $this->assertCount(2, $plans->where('action', 'finish_program'));
    }

    public function testUnexecutedScope(): void
    {
        $pending = Plan::factory()->create(['status' => 'pending']);
        $running = Plan::factory()->create(['status' => 'running']);
        $paused = Plan::factory()->create(['status' => 'paused']);
        $completed = Plan::factory()->create(['status' => 'completed']);
        $cancelled = Plan::factory()->create(['status' => 'cancelled']);
        $failed = Plan::factory()->create(['status' => 'failed']);
        $expired = Plan::factory()->create(['status' => 'expired']);

        $plans = Plan::pendingExecution()->get();

        $this->assertTrue($plans->contains($pending));
        $this->assertTrue($plans->contains($running));
        $this->assertTrue($plans->contains($paused));
        $this->assertFalse($plans->contains($completed));
        $this->assertFalse($plans->contains($cancelled));
        $this->assertFalse($plans->contains($failed));
        $this->assertFalse($plans->contains($expired));
    }

}
