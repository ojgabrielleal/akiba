<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\User;
use App\Models\Activity;
use App\Models\Role;
use App\Models\Calendar;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests from Activity model relationships.
     */
    public function testAuthorRelationship(): void
    {
        $role = Role::factory()->create(['name' => 'administrator']);
        $admin = User::factory()->hasAttached($role, [], 'roles')->create();

        $activity = Activity::factory()
            ->for($admin, 'author')
            ->create();

        $this->assertTrue($activity->author->is($admin));
    }

    public function testConfirmerRelationship(): void
    {
        $role = Role::factory()->create(['name' => 'administrator']);
        $admin = User::factory()->hasAttached($role, [], 'roles')->create();

        $activity = Activity::factory()
            ->for($admin, 'author')
            ->hasAttached(User::factory(5), [], 'confirmations')
            ->create();

        $this->assertCount(5, $activity->confirmations);
        $this->assertContainsOnlyInstancesOf(User::class, $activity->confirmations);
    }

    public function testCalendarRelationship(): void
    {
        $role = Role::factory()->create(['name' => 'administrator']);
        $admin = User::factory()->hasAttached($role, [], 'roles')->create();

        $user = User::factory()->create();

        $activity = Activity::factory()
            ->for($admin, 'author')
            ->has(Calendar::factory()->for($user, 'responsible'))
            ->create();

        $this->assertTrue($activity->calendar->responsible->is($user));
        $this->assertInstanceOf(Calendar::class, $activity->calendar);
    }

    /**
     * Tests from Activity model scopes.
     */
    public function testValidScope(): void
    {
        $role = Role::factory()->create(['name' => 'administrator']);
        $admin = User::factory()->hasAttached($role, [], 'roles')->create();

        $expiredActivity = Activity::factory()
            ->for($admin, 'author')
            ->create(['limit' => now()->subDays(3)]);

        $validActivity = Activity::factory()
            ->for($admin, 'author')
            ->create(['limit' => now()->addDays(3)]);

        $activities = Activity::notExpired()->get();

        $this->assertTrue($activities->contains($validActivity));
        $this->assertFalse($activities->contains($expiredActivity));
    }
}
