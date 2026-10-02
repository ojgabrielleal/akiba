<?php

namespace Tests\Feature\Public;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_awards_open_scheduled_badge_to_authenticated_member(): void
    {
        $user = User::factory()->create();

        Badge::factory()->create([
            'code' => 'presenca_site',
            'type' => Badge::TYPE_SCHEDULED,
            'rule' => [
                'trigger' => 'site.presence_window',
                'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMinute()->format('Y-m-d H:i:s'),
            ],
        ]);

        $response = $this->actingAs($user)->postJson('/presence');

        $response->assertOk()->assertJson(['awarded' => 1]);
        $this->assertDatabaseCount('badge_assignments', 1);
    }

    public function test_endpoint_requires_authenticated_member(): void
    {
        $response = $this->postJson('/presence');

        $response->assertUnauthorized();
    }
}
