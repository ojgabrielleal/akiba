<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    public function store(?Model $notifiable, array $data): PushSubscription
    {
        $data = validator($data, [
            'endpoint' => ['required', 'string'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ])->validate();

        $values = [
            'public_key' => $data['keys']['p256dh'],
            'auth_token' => $data['keys']['auth'],
            'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
        ];

        if ($notifiable) {
            $values['notifiable_type'] = $notifiable->getMorphClass();
            $values['notifiable_id'] = $notifiable->getKey();
        }

        return PushSubscription::query()->updateOrCreate(
            [
                'notifiable_type' => $values['notifiable_type'] ?? null,
                'notifiable_id' => $values['notifiable_id'] ?? null,
                'endpoint' => $data['endpoint'],
            ],
            $values,
        );
    }

    public function storeWithActivationNotification(?Model $notifiable, array $data, string $url = '/'): PushSubscription
    {
        $subscription = $this->store($notifiable, $data);

        $this->sendToSubscription($subscription, [
            'title' => 'Bem vindo(a) ao clube!!!!',
            'body' => 'Obrigado por ativar as notificações da Akiba. Qualquer novidade você será avisado!',
            'icon' => '/favicon.ico',
            'url' => $url,
        ]);

        return $subscription;
    }

    public function destroy(Model $notifiable, string $endpoint): void
    {
        $notifiable->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete();
    }

    public function sendToUserOrAll(?Model $notifiable, array $payload): void
    {
        $subscriptions = PushSubscription::query()
            ->when($notifiable, fn ($query) => $query
                ->where('notifiable_type', $notifiable->getMorphClass())
                ->where('notifiable_id', $notifiable->getKey()))
            ->get()
            ->unique('endpoint')
            ->values();

        $this->sendToSubscriptions($subscriptions, [
            'audience' => $notifiable ? 'user' : 'all',
            ...$payload,
        ]);
    }

    public function sendToSubscription(PushSubscription $subscription, array $payload): void
    {
        $this->sendToSubscriptions(collect([$subscription]), [
            'audience' => 'user',
            ...$payload,
        ]);
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
