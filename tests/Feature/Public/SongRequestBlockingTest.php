<?php

namespace Tests\Feature\Public;

use App\Models\{OAuthAccount, Onair, Permission, Poll, PollOption, Post, Program, Role, SongRequest, SongRequestBlock, User};
use App\Services\{CommentService, OnairService, PollService, SongRequestBlockService, SongRequestService, PushNotificationService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SongRequestBlockingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(PushNotificationService::class)->shouldReceive('sendToUserOrAll')->andReturnNull();
    }

    private function onair(): Onair
    {
        return Onair::factory()->live()->for(Program::factory()->for(User::factory(), 'host'), 'program')->create();
    }

    private function host(array $permissions = ['song.request.block', 'song.request.unblock']): User
    {
        $role = Role::factory()->create();
        foreach ($permissions as $name) {
            $role->permissions()->attach(Permission::firstOrCreate(['name' => $name], ['label' => $name]));
        }
        $host = User::factory()->create();
        $host->roles()->attach($role);
        return $host;
    }

    private function payload(): array
    {
        return ['request_mode' => 'music', 'music' => ['name' => 'Opening', 'production' => 'Anime Test', 'type' => 'OP']];
    }

    public function test_authenticated_listener_can_submit_when_open(): void
    {
        $this->onair();
        $this->actingAs(User::factory()->create())->post('/song-request', $this->payload())->assertStatus(303);
        $this->assertDatabaseCount('song_requests', 1);
    }

    public function test_block_rejects_direct_submission_but_other_listener_can_submit(): void
    {
        $this->onair();
        $listener = User::factory()->create();
        app(SongRequestBlockService::class)->block($listener);
        $this->actingAs($listener)->postJson('/song-request', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('song_request');
        $this->assertDatabaseCount('song_requests', 0);
        $this->assertDatabaseCount((new \App\Models\Music)->getTable(), 0);
        $this->actingAs(User::factory()->create())->post('/song-request', $this->payload())->assertStatus(303);
        $this->assertDatabaseCount('song_requests', 1);
    }

    public function test_authorized_host_can_block_and_unblock_without_changing_completed_requests(): void
    {
        $onair = $this->onair();
        $listener = User::factory()->create();
        $old = SongRequest::factory()->for($onair)->for($listener, 'requester')->create(['type' => 'music', 'was_reproduced' => true]);
        $before = $old->refresh()->getRawOriginal();
        $this->actingAs($this->host())->put('/panel/locution/song-request/'.$old->uuid.'/requester/block')->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 1);
        $this->put('/panel/locution/song-request/'.$old->uuid.'/requester/block')->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 1);
        $this->assertSame($before, $old->refresh()->getRawOriginal());
        $this->delete('/panel/locution/song-request/requester/block/'.SongRequestBlock::sole()->id)->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 0);
        $this->actingAs($listener)->post('/song-request', $this->payload())->assertStatus(303);
    }

    public function test_block_cancels_all_pending_items_only_for_the_target_identity(): void
    {
        $first = $this->onair();
        $second = $this->onair();
        $member = User::factory()->create();
        $listener = OAuthAccount::factory()->create(['id' => $member->id]);
        $pending = SongRequest::factory()->for($first)->for($listener, 'requester')->create(['type' => 'music']);
        $otherPending = SongRequest::factory()->for($second)->for($listener, 'requester')->create(['type' => 'music']);
        $message = SongRequest::factory()->for($first)->for($listener, 'requester')->create(['type' => 'message']);
        $completed = SongRequest::factory()->for($first)->for($listener, 'requester')->create(['type' => 'music', 'was_reproduced' => true]);
        $read = SongRequest::factory()->for($first)->for($listener, 'requester')->create(['type' => 'message', 'was_read' => true]);
        $unrelated = SongRequest::factory()->for($first)->for($member, 'requester')->create(['type' => 'music']);
        $countBefore = $first->song_requests_total;

        $this->actingAs($this->host())->put('/panel/locution/song-request/'.$pending->uuid.'/requester/block')->assertRedirect();

        $this->assertTrue($pending->refresh()->was_canceled);
        $this->assertTrue($otherPending->refresh()->was_canceled);
        $this->assertTrue($message->refresh()->was_dismissed);
        $this->assertFalse($completed->refresh()->was_canceled);
        $this->assertFalse($read->refresh()->was_dismissed);
        $this->assertFalse($unrelated->refresh()->was_canceled);
        $this->assertSame($countBefore, $first->refresh()->song_requests_total);
        $this->assertDatabaseCount('song_requests', 6);

        $this->put('/panel/locution/song-request/'.$pending->uuid.'/requester/block')->assertRedirect();
        $this->assertSame($countBefore, $first->refresh()->song_requests_total);
        $this->delete('/panel/locution/song-request/requester/block/'.SongRequestBlock::sole()->id)->assertRedirect();
        $this->assertTrue($pending->refresh()->was_canceled);
        $this->assertTrue($message->refresh()->was_dismissed);
    }

    public function test_block_and_unblock_require_separate_permissions(): void
    {
        $old = SongRequest::factory()->for($this->onair())->for(User::factory(), 'requester')->create();
        $blockUrl = '/panel/locution/song-request/'.$old->uuid.'/requester/block';
        $this->actingAs($this->host(['song.request.toggle']))->put($blockUrl)->assertForbidden();
        $this->actingAs($this->host(['song.request.block']))->put($blockUrl)->assertRedirect();
        $unblockUrl = '/panel/locution/song-request/requester/block/'.SongRequestBlock::sole()->id;
        $this->delete($unblockUrl)->assertForbidden();
        $this->actingAs($this->host(['song.request.unblock']))->put($blockUrl)->assertForbidden();
        $this->delete($unblockUrl)->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 0);
    }

    public function test_administration_lists_and_unblocks_requesters_with_moderation_permissions(): void
    {
        $listener = User::factory()->create(['nickname' => 'Ouvinte bloqueado']);
        $host = $this->host(['audience.moderation.list', 'audience.moderation.unblock']);
        app(SongRequestBlockService::class)->block($listener, $host, reason: 'Spam nos pedidos');

        $this->actingAs($host)->get('/panel/administration')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('private/Administration', false)
                ->where('blockedRequesters.0.name', 'Ouvinte bloqueado')
                ->where('blockedRequesters.0.blocked_by', $host->nickname)
                ->where('blockedRequesters.0.reason', 'Spam nos pedidos')
            );

        $block = SongRequestBlock::sole();
        $this->assertSame($host->id, $block->blocked_by_id);
        $this->delete('/panel/administration/song-request/requester/block/'.$block->id)->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 0);
    }

    public function test_administration_can_block_the_same_oauth_account_by_module(): void
    {
        $account = OAuthAccount::factory()->create();
        $host = $this->host(['audience.moderation.block']);

        $this->actingAs($host)->post('/panel/administration/audience-block', [
            'oauth_account' => $account->uuid,
            'reason' => 'Envio repetido de spam',
            'modules' => [SongRequestBlockService::MODULE_SONG_REQUESTS, SongRequestBlockService::MODULE_POLLS],
        ])->assertRedirect();
        $this->assertDatabaseCount('song_request_blocks', 2);
        $this->assertTrue(app(SongRequestBlockService::class)->isBlocked($account));
        $this->assertTrue(app(SongRequestBlockService::class)->isBlocked($account, SongRequestBlockService::MODULE_POLLS));
        $this->assertDatabaseHas('song_request_blocks', [
            'requester_type' => $account->getMorphClass(),
            'requester_id' => $account->id,
            'reason' => 'Envio repetido de spam',
            'module' => SongRequestBlockService::MODULE_SONG_REQUESTS,
            'blocked_by_id' => $host->id,
        ]);
        $this->assertDatabaseHas('song_request_blocks', [
            'requester_type' => $account->getMorphClass(),
            'requester_id' => $account->id,
            'reason' => 'Envio repetido de spam',
            'module' => SongRequestBlockService::MODULE_POLLS,
            'blocked_by_id' => $host->id,
        ]);
    }

    public function test_administration_rejects_invalid_block_reasons(): void
    {
        $account = OAuthAccount::factory()->create();
        $this->actingAs($this->host(['audience.moderation.block']));

        foreach ([null, '', '   ', str_repeat('a', 1001), ['invalid']] as $reason) {
            $this->postJson('/panel/administration/audience-block', [
                'oauth_account' => $account->uuid,
                'modules' => [SongRequestBlockService::MODULE_SONG_REQUESTS],
                'reason' => $reason,
            ])->assertUnprocessable()->assertJsonValidationErrors('reason');
        }

        $this->assertDatabaseCount('song_request_blocks', 0);
    }

    public function test_administration_rejects_invalid_modules_without_creating_blocks(): void
    {
        $account = OAuthAccount::factory()->create();
        $this->actingAs($this->host(['audience.moderation.block']));

        foreach ([[], ['song_requests', 'invalid'], ['polls', 'polls'], 'polls'] as $modules) {
            $this->postJson('/panel/administration/audience-block', [
                'oauth_account' => $account->uuid,
                'modules' => $modules,
                'reason' => 'Spam',
            ])->assertUnprocessable();
        }

        $this->assertDatabaseCount('song_request_blocks', 0);
    }

    public function test_multiple_module_block_preserves_existing_block_reason(): void
    {
        $account = OAuthAccount::factory()->create();
        $host = $this->host(['audience.moderation.block']);
        app(SongRequestBlockService::class)->block($account, $host, 'polls', 'Motivo original');

        $this->actingAs($host)->post('/panel/administration/audience-block', [
            'oauth_account' => $account->uuid,
            'modules' => ['song_requests', 'polls', 'comments'],
            'reason' => 'Novo motivo',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseCount('song_request_blocks', 3);
        $this->assertDatabaseHas('song_request_blocks', ['module' => 'polls', 'reason' => 'Motivo original']);
        $this->assertDatabaseHas('song_request_blocks', ['module' => 'comments', 'reason' => 'Novo motivo']);
    }

    public function test_module_blocks_do_not_leak_into_other_modules(): void
    {
        $account = OAuthAccount::factory()->create();
        app(SongRequestBlockService::class)->block(
            $account,
            null,
            SongRequestBlockService::MODULE_POLLS,
        );

        $this->assertTrue(app(SongRequestBlockService::class)->isBlocked($account, SongRequestBlockService::MODULE_POLLS));
        $this->assertFalse(app(SongRequestBlockService::class)->isBlocked($account));
    }

    public function test_poll_module_block_rejects_vote(): void
    {
        $account = OAuthAccount::factory()->create();
        $option = PollOption::factory()->for(Poll::factory(), 'poll')->create();
        app(SongRequestBlockService::class)->block(
            $account,
            null,
            SongRequestBlockService::MODULE_POLLS,
        );

        $this->expectException(ValidationException::class);
        app(PollService::class)->storeVote($option, $account);
    }

    public function test_comment_module_block_rejects_comment_without_affecting_other_modules(): void
    {
        $account = OAuthAccount::factory()->create();
        app(SongRequestBlockService::class)->block(
            $account,
            null,
            SongRequestBlockService::MODULE_COMMENTS,
        );

        $this->assertFalse(app(SongRequestBlockService::class)->isBlocked($account));
        $this->assertFalse(app(SongRequestBlockService::class)->isBlocked($account, SongRequestBlockService::MODULE_POLLS));

        try {
            app(CommentService::class)->store(Post::factory()->create(), $account, [
                'comment' => 'Comentário bloqueado',
            ]);
            $this->fail('O comentário deveria ter sido rejeitado.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('comment', $exception->errors());
        }

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_cannot_block_requester(): void
    {
        $old = SongRequest::factory()->for($this->onair())->for(User::factory(), 'requester')->create();
        $this->putJson('/panel/locution/song-request/'.$old->uuid.'/requester/block')->assertUnauthorized();
        $this->assertDatabaseCount('song_request_blocks', 0);
    }

    public function test_regular_user_cannot_block_or_unblock(): void
    {
        $old = SongRequest::factory()->for($this->onair())->for(User::factory(), 'requester')->create();
        $this->actingAs(User::factory()->create())->put('/panel/locution/song-request/'.$old->uuid.'/requester/block')->assertForbidden();
        app(SongRequestBlockService::class)->block($old->requester);
        $this->delete('/panel/locution/song-request/requester/block/'.SongRequestBlock::sole()->id)->assertForbidden();
        $this->assertDatabaseCount('song_request_blocks', 1);
    }

    public function test_block_survives_next_transmission_and_state_is_specific_to_listener(): void
    {
        $first = $this->onair();
        $listener = User::factory()->create();
        app(SongRequestBlockService::class)->block($listener);
        $first->update(['in_air' => false]);
        $this->onair();
        $this->actingAs($listener)->postJson('/song-request', $this->payload())->assertUnprocessable();
        $service = app(OnairService::class);
        $this->assertSame(1, (int) $service->filter(['live' => true, 'requester' => $listener, 'first' => true])->song_requests_blocked);
        $this->assertNull($service->filter(['live' => true, 'requester' => User::factory()->create(), 'first' => true])->song_requests_blocked);
    }

    public function test_oauth_identity_is_blocked_without_blocking_member_with_same_id(): void
    {
        $this->onair();
        $member = User::factory()->create();
        $oauth = OAuthAccount::factory()->create(['id' => $member->id]);
        app(SongRequestBlockService::class)->block($oauth);
        $this->assertFalse(app(SongRequestBlockService::class)->isBlocked($member));
        $this->expectException(ValidationException::class);
        app(SongRequestService::class)->store($this->payload(), $oauth);
    }

    public function test_global_closure_still_rejects_stale_page_submission(): void
    {
        $onair = $this->onair();
        $onair->update(['allows_song_requests' => false]);
        $this->actingAs(User::factory()->create())->postJson('/song-request', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('song_request');
        $this->assertDatabaseCount('song_requests', 0);
    }
}
