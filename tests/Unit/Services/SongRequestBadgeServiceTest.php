<?php

namespace Tests\Unit\Services;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\OAuthAccount;
use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;
use App\Models\User;
use App\Services\SongRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongRequestBadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_transfers_most_requests_badge_to_unique_leader(): void
    {
        Badge::factory()->stealable()->create([
            'code' => 'maior_pedinte_musical',
            'source' => 'song_request',
            'rule' => ['trigger' => 'song_request.most_requests'],
        ]);

        $host = User::factory()->create();
        $program = Program::factory()->for($host, 'host')->create();
        $onair = Onair::factory()->for($program, 'program')->create([
            'in_air' => true,
            'allows_song_requests' => true,
        ]);
        $leader = OAuthAccount::factory()->create();
        $challenger = OAuthAccount::factory()->create();
        $service = app(SongRequestService::class);

        SongRequest::factory()
            ->for($onair, 'onair')
            ->for($leader, 'requester')
            ->create();

        $service->store(['message' => 'Primeiro pedido'], $challenger);

        $this->assertDatabaseCount('badge_assignments', 0);

        $service->store(['message' => 'Segundo pedido'], $challenger);

        $assignment = BadgeAssignment::query()->with('owner')->first();

        $this->assertTrue($assignment->owner->is($challenger));
        $this->assertNull($assignment->revoked_at);
    }
}
