<?php

namespace App\Integrations;

use App\Models\RadioStation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AudienceService
{
    public function get(RadioStation $radioStation): array
    {
        $startedAt = microtime(true);

        try {
            $response = Http::timeout(5)
                ->withOptions(['verify' => false])
                ->get($radioStation->endpoint);

            $responseTime = $this->responseTime($startedAt);

            if ($response->failed()) {
                return $this->unavailable('offline', $responseTime);
            }

            $listeners = data_get($response->json(), $radioStation->listeners_path);

            if (!is_numeric($listeners)) {
                return $this->unavailable('invalid_response', $responseTime);
            }

            return [
                'listeners' => max(0, (int) $listeners),
                'status' => 'online',
                'response_time_ms' => $responseTime,
            ];
        } catch (Throwable $throwable) {
            Log::error("AudienceService error for {$radioStation->name}: {$throwable->getMessage()}");
            return $this->unavailable('offline', $this->responseTime($startedAt));
        }
    }

    private function unavailable(string $status, int $responseTime): array
    {
        return [
            'listeners' => null,
            'status' => $status,
            'response_time_ms' => $responseTime,
        ];
    }

    private function responseTime(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
