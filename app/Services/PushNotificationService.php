<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    public function store(User $user, array $data): PushSubscription
    {
        return PushSubscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $data['endpoint'],
            ],
            [
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
            ],
        );
    }

    public function destroy(User $user, string $endpoint): void
    {
        $user->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete();
    }

    public function sendToUserOrAll(?User $user, array $payload): void
    {
        $subscriptions = PushSubscription::query()
            ->when($user, fn ($query) => $query->whereBelongsTo($user))
            ->get();

        $this->sendToSubscriptions($subscriptions, $payload);
    }

    private function sendToSubscriptions(Collection $subscriptions, array $payload): void
    {
        if ($subscriptions->isEmpty() || ! $this->isConfigured()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'keys' => [
                        'p256dh' => $subscription->public_key,
                        'auth' => $subscription->auth_token,
                    ],
                    'contentEncoding' => $subscription->content_encoding,
                ]),
                json_encode($payload, JSON_THROW_ON_ERROR),
            );
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                PushSubscription::query()
                    ->where('endpoint', $report->getRequest()->getUri()->__toString())
                    ->delete();
            }
        }
    }

    private function isConfigured(): bool
    {
        return filled(config('services.webpush.public_key'))
            && filled(config('services.webpush.private_key'))
            && filled(config('services.webpush.subject'));
    }
}
