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
            ->post('/panel/push-notification', [
                'endpoint' => 'https://push.example/subscription',
                'keys' => [
                    'p256dh' => 'public-key',
                    'auth' => 'auth-token',
                ],
                'content_encoding' => 'aesgcm',
                'silent' => true,
            ])
            ->assertNoContent();

        $this->assertDatabaseHas('push_subscriptions', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'endpoint' => 'https://push.example/subscription',
            'public_key' => 'public-key',
            'auth_token' => 'auth-token',
            'content_encoding' => 'aesgcm',
        ]);
    }

    public function test_same_endpoint_can_be_stored_for_different_notifiables(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $payload = [
            'endpoint' => 'https://push.example/shared-subscription',
            'keys' => [
                'p256dh' => 'public-key',
                'auth' => 'auth-token',
            ],
            'content_encoding' => 'aesgcm',
        ];

        app(PushNotificationService::class)->store($firstUser, $payload);
        app(PushNotificationService::class)->store($secondUser, $payload);
        app(PushNotificationService::class)->store($firstUser, [
            ...$payload,
            'keys' => [
                'p256dh' => 'updated-public-key',
                'auth' => 'updated-auth-token',
            ],
        ]);

        $this->assertDatabaseCount('push_subscriptions', 2);
        $this->assertDatabaseHas('push_subscriptions', [
            'notifiable_type' => User::class,
            'notifiable_id' => $firstUser->id,
            'endpoint' => 'https://push.example/shared-subscription',
            'public_key' => 'updated-public-key',
            'auth_token' => 'updated-auth-token',
        ]);
        $this->assertDatabaseHas('push_subscriptions', [
            'notifiable_type' => User::class,
            'notifiable_id' => $secondUser->id,
            'endpoint' => 'https://push.example/shared-subscription',
            'public_key' => 'public-key',
            'auth_token' => 'auth-token',
        ]);
    }

    public function test_user_can_destroy_push_subscription(): void
    {
        $user = User::factory()->create();
        PushSubscription::factory()->for($user, 'notifiable')->create([
            'endpoint' => 'https://push.example/subscription',
        ]);

        $this
            ->actingAs($user)
            ->delete('/panel/push-notification', [
                'endpoint' => 'https://push.example/subscription',
            ])
            ->assertNoContent();

        $this->assertDatabaseMissing('push_subscriptions', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
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
        PushSubscription::factory()->for($user, 'notifiable')->create();

        app(PushNotificationService::class)->sendToUserOrAll($user, [
            'title' => 'Novo pedido musical',
            'body' => 'Um ouvinte pediu uma musica.',
        ]);

        $this->assertDatabaseCount('push_subscriptions', 1);
    }
}
