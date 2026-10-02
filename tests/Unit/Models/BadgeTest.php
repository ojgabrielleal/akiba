<?php

namespace Tests\Unit\Models;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_relationship(): void
    {
        $badge = Badge::factory()->create();
        $owner = User::factory()->create();

        BadgeAssignment::factory()
            ->forBadgeAndOwner($badge, $owner)
            ->create();

        $this->assertCount(1, $badge->assignments);
        $this->assertContainsOnlyInstancesOf(BadgeAssignment::class, $badge->assignments);
    }

    public function test_active_scope_filters_active_badges(): void
    {
        Badge::factory()->create(['is_active' => true]);
        Badge::factory()->create(['is_active' => false]);

        $this->assertCount(1, Badge::active()->get());
    }

    public function test_type_scopes_filter_badges(): void
    {
        Badge::factory()->fixed()->create();
        Badge::factory()->stealable()->create();

        $this->assertCount(1, Badge::fixed()->get());
        $this->assertCount(1, Badge::stealable()->get());
    }
}
