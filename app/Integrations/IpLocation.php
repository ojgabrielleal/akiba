<?php

namespace App\Integrations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class IpLocation
{
    public function find(?string $ip): array
    {
        $publicIp = $this->isPublicIp($ip) ? $ip : null;

        if ($publicIp === null && ! config('services.geoip.resolve_private_ips', false)) {
            return [];
        }

        $cacheKey = 'page-view-location:'.hash('sha256', $publicIp ?? 'local-egress');

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey, []);
        }

        $location = $this->request($publicIp);
        $expiresAt = $location === []
            ? now()->addMinutes((int) config('services.geoip.failure_cache_minutes', 5))
            : now()->addDays((int) config('services.geoip.cache_days', 30));

        Cache::put($cacheKey, $location, $expiresAt);

        return $location;
    }

    private function request(?string $ip): array
    {
        try {
            $url = rtrim((string) config('services.geoip.url'), '/');

            if ($ip !== null) {
                $url .= '/'.rawurlencode($ip);
            }

            $response = Http::connectTimeout((float) config('services.geoip.connect_timeout', 1))
                ->timeout((float) config('services.geoip.timeout', 2))
                ->acceptJson()
                ->get($url, [
                    'fields' => 'city,subdivision,location',
                ]);

            if ($response->failed()) {
                Log::warning('GeoIP lookup returned an error status.', [
                    'status' => $response->status(),
                ]);

                return [];
            }

            $data = $response->json();

            if (! is_array($data)) {
                return [];
            }

            return array_filter([
                'country_code' => $this->string($data['country'] ?? null, 2, true),
                'region_code' => $this->string($data['subdivision'] ?? null, 16, true),
                'city' => $this->string($data['city'] ?? null, 120),
                'latitude' => $this->coordinate($data['location']['latitude'] ?? null, -90, 90),
                'longitude' => $this->coordinate($data['location']['longitude'] ?? null, -180, 180),
                'location_accuracy_radius' => $this->positiveInteger($data['location']['accuracy_radius'] ?? null),
                'timezone' => $this->string($data['location']['time_zone'] ?? null, 64),
            ], fn (mixed $value): bool => $value !== null);
        } catch (Throwable $exception) {
            Log::warning('GeoIP lookup failed.', [
                'exception' => $exception::class,
            ]);

            return [];
        }
    }

    private function isPublicIp(?string $ip): bool
    {
        return is_string($ip) && filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }

    private function string(mixed $value, int $maxLength, bool $uppercase = false): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = mb_substr(trim($value), 0, $maxLength);

        return $uppercase ? mb_strtoupper($value) : $value;
    }

    private function coordinate(mixed $value, float $minimum, float $maximum): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $coordinate = (float) $value;

        return $coordinate >= $minimum && $coordinate <= $maximum ? $coordinate : null;
    }

    private function positiveInteger(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value >= 0 ? (int) $value : null;
    }
}
