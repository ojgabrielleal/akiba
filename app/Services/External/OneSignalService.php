<?php

namespace App\Services\External;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OneSignalService
{
    public function sendToAll(string $title, string $message, ?string $url = null, array $data = [], array $options = []): ?array
    {
        return $this->sendToSegments(['Subscribed Users'], $title, $message, $url, $data, $options);
    }

    public function sendToSegments(array $segments, string $title, string $message, ?string $url = null, array $data = [], array $options = []): ?array
    {
        return $this->send([
            'included_segments' => array_values($segments),
        ], $title, $message, $url, $data, $options);
    }

    public function sendToExternalIds(array $externalIds, string $title, string $message, ?string $url = null, array $data = [], array $options = []): ?array
    {
        return $this->send([
            'include_aliases' => [
                'external_id' => array_values(array_map('strval', $externalIds)),
            ],
        ], $title, $message, $url, $data, $options);
    }

    public function send(array $target, string $title, string $message, ?string $url = null, array $data = [], array $options = []): ?array
    {
        try {
            $this->ensureConfigured();

            $payload = array_filter([
                'app_id' => config('services.onesignal.app_id'),
                'target_channel' => 'push',
                'headings' => $this->localizedText($title),
                'contents' => $this->localizedText($message),
                'url' => $url,
                'data' => $data ?: null,
            ], fn ($value) => $value !== null);

            $payload = array_merge($payload, $target, $this->normalizeOptions($options));

            $response = $this->request($payload);

            if ($response->failed()) {
                Log::warning('OneSignal notification failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $exception) {
            Log::error('OneSignal notification error: ' . $exception->getMessage());

            return null;
        }
    }

    private function request(array $payload): Response
    {
        return Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Key ' . config('services.onesignal.rest_api_key'),
                'Content-Type' => 'application/json',
            ])
            ->post(rtrim(config('services.onesignal.api_url'), '/') . '/notifications', $payload);
    }

    private function localizedText(string $text): array
    {
        return [
            'en' => $text,
            'pt' => $text,
        ];
    }

    private function normalizeOptions(array $options): array
    {
        $aliases = [
            'icon' => 'chrome_web_icon',
            'image' => 'chrome_web_image',
            'badge' => 'chrome_web_badge',
        ];

        foreach ($aliases as $alias => $oneSignalField) {
            if (array_key_exists($alias, $options)) {
                $options[$oneSignalField] = $options[$alias];
                unset($options[$alias]);
            }
        }

        return array_filter($options, fn ($value) => $value !== null);
    }

    private function ensureConfigured(): void
    {
        $missing = collect([
            'ONESIGNAL_APP_ID' => config('services.onesignal.app_id'),
            'ONESIGNAL_REST_API_KEY' => config('services.onesignal.rest_api_key'),
        ])
            ->filter(fn ($value) => blank($value))
            ->keys()
            ->all();

        if ($missing) {
            throw new InvalidArgumentException('Missing OneSignal config: ' . Arr::join($missing, ', '));
        }
    }
}
