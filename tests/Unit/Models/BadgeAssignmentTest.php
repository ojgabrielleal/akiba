<?php

namespace Tests\Unit\Models;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_relationship(): void
    {
        $badge = Badge::factory()->create();
        $owner = User::factory()->create();

        $assignment = BadgeAssignment::factory()
            ->forBadgeAndOwner($badge, $owner)
            ->create();

        $this->assertTrue($assignment->badge->is($badge));
    }

    public function test_owner_relationship(): void
    {
        $badge = Badge::factory()->create();
        $owner = User::factory()->create();

        $assignment = BadgeAssignment::factory()
            ->forBadgeAndOwner($badge, $owner)
            ->create();

        $this->assertTrue($assignment->owner->is($owner));
    }

    public function test_active_and_revoked_scopes(): void
    {
        BadgeAssignment::factory()->create(['revoked_at' => null]);
        BadgeAssignment::factory()->create(['revoked_at' => now()]);

        $this->assertCount(1, BadgeAssignment::active()->get());
        $this->assertCount(1, BadgeAssignment::revoked()->get());
    }
}
