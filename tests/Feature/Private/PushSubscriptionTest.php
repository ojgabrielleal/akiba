<?php

namespace Tests\Feature\Private;

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_push_subscription(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->post('/panel/push-subscription', [
                'endpoint' => 'https://push.example/subscription',
                'keys' => [
                    'p256dh' => 'public-key',
                    'auth' => 'auth-token',
                ],
                'content_encoding' => 'aesgcm',
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://push.example/subscription',
            'public_key' => 'public-key',
            'auth_token' => 'auth-token',
            'content_encoding' => 'aesgcm',
        ]);
    }

    public function test_user_can_destroy_push_subscription(): void
    {
        $user = User::factory()->create();
        PushSubscription::factory()->for($user)->create([
            'endpoint' => 'https://push.example/subscription',
        ]);

        $this
            ->actingAs($user)
            ->delete('/panel/push-subscription', [
                'endpoint' => 'https://push.example/subscription',
            ])
            ->assertNoContent();

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => 'https://push.example/subscription',
        ]);
    }

    public function test_push_send_is_skipped_without_vapid_configuration(): void
    {
        config([
            'services.webpush.public_key' => null,
            'services.webpush.private_key' => null,
            'services.webpush.subject' => null,
        ]);

        $user = User::factory()->create();
        PushSubscription::factory()->for($user)->create();

        app(PushNotificationService::class)->sendToUserOrAll($user, [
            'title' => 'Novo pedido musical',
            'body' => 'Um ouvinte pediu uma musica.',
        ]);

        $this->assertDatabaseCount('push_subscriptions', 1);
    }
}
