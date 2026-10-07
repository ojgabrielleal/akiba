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
        $timeout = (float) config('services.audience.timeout', 2);
        $connectTimeout = (float) config('services.audience.connect_timeout', 1);

        try {
            $response = Http::connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->withOptions(['verify' => false])
                ->get($radioStation->endpoint);

            $responseTime = $this->responseTime($startedAt);

            if ($response->failed()) {
                return $this->unavailable('offline', $responseTime);
            }

            $listeners = $this->listeners($response->json(), $radioStation->listeners_path);

            if ($listeners === null) {
                return $this->unavailable('invalid_response', $responseTime);
            }

            return [
                'listeners' => $listeners,
                'status' => 'online',
                'response_time_ms' => $responseTime,
            ];
        } catch (Throwable $throwable) {
            Log::warning('Audience collection failed.', [
                'station' => $radioStation->name,
                'endpoint' => $radioStation->endpoint,
                'error' => $throwable->getMessage(),
            ]);

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

    private function listeners(array $payload, string $path): ?int
    {
        $value = data_get($payload, $path);

        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        if (! is_array($value)) {
            return null;
        }

        $listeners = collect($value)
            ->flatten()
            ->filter(fn ($item) => is_numeric($item))
            ->sum(fn ($item) => max(0, (int) $item));

        return is_numeric($listeners) ? (int) $listeners : null;
    }

    private function responseTime(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
