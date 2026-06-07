<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    private $baseUrl = 'https://api.onesignal.com';

    public function sendPush(string $title, string $message, string $url)
    {
        if (!app()->environment('production')) return false;

        $payload = [
            'app_id' => config('services.onesignal.app_id'),
            'included_segments' => ['All'],
            'url' => $url,
            //'chrome_web_icon' => $icon,
            //'firefox_icon' => $icon,
            'headings' => [
                'en' => $title,
            ],
            'contents' => [
                'en' => $message,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.onesignal.api_key'),
            'Content-Type' => 'application/json',
        ])->withOptions([
            'verify' => false,
        ])->post("{$this->baseUrl}/notifications?c=push", $payload);

        if ($response->failed()) {
            Log::error('OneSignal push failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return $response->json();
    }
}
