<?php

namespace Tests\Unit\Services;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\OAuthAccount;
use App\Models\User;
use App\Services\EnigmaGameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnigmaGameBadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_final_answer_transfers_most_wins_badge_to_unique_leader(): void
    {
        Badge::factory()->stealable()->create([
            'code' => 'campeao_enigma_otaku',
            'source' => 'enigmagame',
            'rule' => ['trigger' => 'enigmagame.most_wins'],
        ]);

        $game = EnigmaGame::factory()->create(['status' => EnigmaGame::STATUS_ACTIVE]);
        $leader = OAuthAccount::factory()->create();
        $challenger = OAuthAccount::factory()->create();
        $responder = User::factory()->create();
        $service = app(EnigmaGameService::class);

        EnigmaGameInteraction::factory()
            ->correct($responder)
            ->for($game, 'enigmagame')
            ->for($leader, 'participant')
            ->create();

        $service->respond(
            EnigmaGameInteraction::factory()
                ->finalAnswer()
                ->for($game, 'enigmagame')
                ->for($challenger, 'participant')
                ->create(),
            $responder,
            ['result' => 'correct'],
        );

        $this->assertDatabaseCount('badge_assignments', 0);

        $service->respond(
            EnigmaGameInteraction::factory()
                ->finalAnswer()
                ->for($game, 'enigmagame')
                ->for($challenger, 'participant')
                ->create(),
            $responder,
            ['result' => 'correct'],
        );

        $assignment = BadgeAssignment::query()->with('owner')->first();

        $this->assertTrue($assignment->owner->is($challenger));
        $this->assertNull($assignment->revoked_at);
    }
}
