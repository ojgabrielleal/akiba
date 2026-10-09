<?php 

namespace App\Integrations;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StreamService
{
    private const CACHE_KEY = 'radio.stream.metadata';
    private const CACHE_TTL_SECONDS = 30;
    private const STALE_CACHE_TTL_SECONDS = 3600;

    protected $url;

    public function data()
    {
        if (Cache::has(self::CACHE_KEY . '.fresh')) {
            return Cache::get(self::CACHE_KEY . '.fresh');
        }

        try {
            $url = config('services.stream.metadata');

            if (!$url) {
                Log::warning('Radio API error: STREAM_METADATA is not configured in .env');
                return $this->cachedFallback();
            }

            $response = Http::connectTimeout(2)
                ->timeout(5)
                ->retry(2, 250, throw: false)
                ->acceptJson()
                ->withOptions([
                    'verify' => false,
                ])
                ->get($url);

            if ($response->failed()) {
                Log::warning('Radio API returned error status', [
                    'status' => $response->status(),
                    'url' => $url,
                    'body' => $response->body(),
                ]);

                return $this->cachedFallback();
            }

            $data = $response->json();

            if (!is_array($data)) {
                Log::warning('Radio API returned unexpected data format', [
                    'url' => $url,
                    'body' => $response->body(),
                ]);

                return $this->cachedFallback();
            }

            $stream = [
                'status' => ($data['status'] ?? null) === 'Ligado' ? 'Online' : 'Offline',
                'listeners' => $data['ouvintes_conectados'] ?? 0,
                'bitrate' => $data['plano_bitrate'] ?? 'N/A',
                'current_song' => [
                    'music' => $this->normalizeRadioMetadata($data['musica_atual'] ?? $data['musica_tocando'] ?? 'Desconhecido'),
                    'cover' => $data['capa_musica'] ?? null,
                ],
                'next_song' => [
                    'music' => isset($data['proxima_musica']) ? $this->normalizeRadioMetadata($data['proxima_musica']) : null,
                ],
            ];

            Cache::put(self::CACHE_KEY, $stream, now()->addSeconds(self::STALE_CACHE_TTL_SECONDS));
            Cache::put(self::CACHE_KEY . '.fresh', $stream, now()->addSeconds(self::CACHE_TTL_SECONDS));

            return $stream;
        } catch (\Throwable $e) {
            Log::error('Radio API error', [
                'message' => $e->getMessage(),
                'url' => config('services.stream.metadata'),
            ]);

            return $this->cachedFallback();
        }
    }

    public function normalizeRadioMetadata(string $value): string
    {
        if (!preg_match('/\\\\x[0-9A-Fa-f]{2}/', $value)) {
            return $value;
        }

        return preg_replace_callback('/(?:\\\\x[0-9A-Fa-f]{2})+/', function (array $matches): string {
            $escapedBytes = $matches[0];
            $hex = str_replace('\x', '', $escapedBytes);
            $bytes = hex2bin($hex);

            if ($bytes === false || preg_match('//u', $bytes) !== 1) {
                return $escapedBytes;
            }

            return $bytes;
        }, $value) ?? $value;
    }

    protected function cachedFallback(): array
    {
        return Cache::get(self::CACHE_KEY, $this->fallbackData());
    }

    protected function fallbackData(): array
    {
        return [
            'status' => 'Offline',
            'listeners' => 0,
            'bitrate' => 'N/A',
            'current_song' => [
                'music' => null,
                'cover' => null,
            ],
            'next_song' => [
                'music' => null,
            ],
        ];
    }
}
