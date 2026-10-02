<?php

namespace Tests\Unit\Services;

use App\Models\Badge;
use App\Models\BadgeAssignment;
use App\Models\OAuthAccount;
use App\Models\User;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_fixed_badge_creates_active_assignment(): void
    {
        Badge::factory()->fixed()->create(['code' => 'enigma-solver']);
        $owner = User::factory()->create();
        $awarder = User::factory()->create();
        $service = app(BadgeService::class);

        $assignment = $service->awardFixed('enigma-solver', $owner, 'Resolveu um enigma.', [
            'enigmagame_id' => 10,
        ], $awarder);

        $this->assertInstanceOf(BadgeAssignment::class, $assignment);
        $this->assertTrue($assignment->owner->is($owner));
        $this->assertTrue($assignment->awardedBy->is($awarder));
        $this->assertSame(['enigmagame_id' => 10], $assignment->metadata);
        $this->assertNull($assignment->revoked_at);
    }

    public function test_award_fixed_badge_does_not_duplicate_active_owner_assignment(): void
    {
        Badge::factory()->fixed()->create(['code' => 'enigma-solver']);
        $owner = User::factory()->create();
        $service = app(BadgeService::class);

        $first = $service->awardFixed('enigma-solver', $owner);
        $second = $service->awardFixed('enigma-solver', $owner);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('badge_assignments', 1);
    }

    public function test_transfer_stealable_badge_revokes_previous_owner_and_assigns_new_owner(): void
    {
        Badge::factory()->stealable()->create(['code' => 'enigma-champion']);
        $previousOwner = User::factory()->create();
        $newOwner = User::factory()->create();
        $service = app(BadgeService::class);

        $previousAssignment = $service->transferStealable('enigma-champion', $previousOwner, 'Primeiro campeao.');
        $newAssignment = $service->transferStealable('enigma-champion', $newOwner, 'Novo campeao.');

        $this->assertNotSame($previousAssignment->id, $newAssignment->id);
        $this->assertNotNull($previousAssignment->fresh()->revoked_at);
        $this->assertNull($newAssignment->fresh()->revoked_at);
        $this->assertTrue($service->currentOwner('enigma-champion')->is($newOwner));
        $this->assertDatabaseCount('badge_assignments', 2);
    }

    public function test_transfer_stealable_badge_to_current_owner_is_idempotent(): void
    {
        Badge::factory()->stealable()->create(['code' => 'enigma-champion']);
        $owner = User::factory()->create();
        $service = app(BadgeService::class);

        $first = $service->transferStealable('enigma-champion', $owner);
        $second = $service->transferStealable('enigma-champion', $owner);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('badge_assignments', 1);
    }

    public function test_throws_when_awarding_wrong_badge_type(): void
    {
        Badge::factory()->stealable()->create(['code' => 'enigma-champion']);
        $owner = User::factory()->create();
        $service = app(BadgeService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->awardFixed('enigma-champion', $owner);
    }

    public function test_owned_by_returns_active_assignments_for_user_and_oauth_account(): void
    {
        Badge::factory()->fixed()->create(['code' => 'profile-complete']);
        $user = User::factory()->create();
        $account = OAuthAccount::factory()->create();
        $service = app(BadgeService::class);

        $service->awardFixed('profile-complete', $user);
        $service->awardFixed('profile-complete', $account);

        $this->assertCount(1, $service->ownedBy($user));
        $this->assertCount(1, $service->ownedBy($account));
    }

    public function test_history_keeps_revoked_stealable_assignments(): void
    {
        Badge::factory()->stealable()->create(['code' => 'enigma-champion']);
        $firstOwner = User::factory()->create();
        $secondOwner = User::factory()->create();
        $service = app(BadgeService::class);

        $service->transferStealable('enigma-champion', $firstOwner);
        $service->transferStealable('enigma-champion', $secondOwner);

        $history = $service->history('enigma-champion');

        $this->assertCount(2, $history);
        $this->assertTrue($history->contains(fn (BadgeAssignment $assignment) => $assignment->owner->is($firstOwner) && $assignment->revoked_at !== null));
        $this->assertTrue($history->contains(fn (BadgeAssignment $assignment) => $assignment->owner->is($secondOwner) && $assignment->revoked_at === null));
    }

    public function test_award_scheduled_presence_awards_open_scheduled_badge(): void
    {
        $owner = OAuthAccount::factory()->create();
        $service = app(BadgeService::class);

        Badge::factory()->create([
            'code' => 'presenca_especial',
            'type' => Badge::TYPE_SCHEDULED,
            'rule' => [
                'trigger' => 'site.presence_window',
                'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMinute()->format('Y-m-d H:i:s'),
            ],
        ]);

        $assignments = $service->awardScheduledPresence($owner);

        $this->assertCount(1, $assignments);
        $this->assertTrue($assignments->first()->owner->is($owner));
    }

    public function test_award_scheduled_presence_ignores_closed_window(): void
    {
        $owner = OAuthAccount::factory()->create();
        $service = app(BadgeService::class);

        Badge::factory()->create([
            'code' => 'presenca_futura',
            'type' => Badge::TYPE_SCHEDULED,
            'rule' => [
                'trigger' => 'site.presence_window',
                'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
            ],
        ]);

        $assignments = $service->awardScheduledPresence($owner);

        $this->assertCount(0, $assignments);
        $this->assertDatabaseCount('badge_assignments', 0);
    }


    public function test_award_scheduled_presence_awards_only_selected_user(): void
    {
        $selectedUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $oauthAccount = OAuthAccount::factory()->create();
        $service = app(BadgeService::class);

        Badge::factory()->create([
            'code' => 'presenca_vip',
            'type' => Badge::TYPE_SCHEDULED,
            'rule' => [
                'trigger' => 'site.presence_window',
                'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMinute()->format('Y-m-d H:i:s'),
                'audience' => 'target',
                'target_type' => 'user',
                'target_uuid' => $selectedUser->uuid,
            ],
        ]);

        $this->assertCount(0, $service->awardScheduledPresence($otherUser));
        $this->assertCount(0, $service->awardScheduledPresence($oauthAccount));
        $this->assertCount(1, $service->awardScheduledPresence($selectedUser));
        $this->assertDatabaseCount('badge_assignments', 1);
    }


    public function test_award_scheduled_presence_awards_selected_oauth_account(): void
    {
        $selectedAccount = OAuthAccount::factory()->create();
        $otherAccount = OAuthAccount::factory()->create();
        $internalUser = User::factory()->create();
        $service = app(BadgeService::class);

        Badge::factory()->create([
            'code' => 'presenca_oauth_vip',
            'type' => Badge::TYPE_SCHEDULED,
            'rule' => [
                'trigger' => 'site.presence_window',
                'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addMinute()->format('Y-m-d H:i:s'),
                'audience' => 'target',
                'target_type' => 'oauth_account',
                'target_uuid' => $selectedAccount->uuid,
            ],
        ]);

        $this->assertCount(0, $service->awardScheduledPresence($otherAccount));
        $this->assertCount(0, $service->awardScheduledPresence($internalUser));
        $this->assertCount(1, $service->awardScheduledPresence($selectedAccount));
        $this->assertDatabaseCount('badge_assignments', 1);
    }


    public function test_store_fixed_badge_awards_selected_target(): void
    {
        $owner = OAuthAccount::factory()->create();
        $service = app(BadgeService::class);

        $badge = $service->store([
            'code' => 'aniversario_akiba_10_anos',
            'name' => 'Aniversário Akiba 10 anos',
            'type' => Badge::TYPE_FIXED,
            'audience' => 'target',
            'target_type' => 'oauth_account',
            'target_uuid' => $owner->uuid,
        ]);

        $assignment = BadgeAssignment::query()->with('owner')->first();

        $this->assertSame($badge->id, $assignment->badge_id);
        $this->assertTrue($assignment->owner->is($owner));
        $this->assertDatabaseCount('badge_assignments', 1);
    }


    public function test_destroy_deletes_badge(): void
    {
        $badge = Badge::factory()->create();
        $service = app(BadgeService::class);

        $service->destroy($badge);

        $this->assertDatabaseMissing('badges', [
            'id' => $badge->id,
        ]);
    }

}
