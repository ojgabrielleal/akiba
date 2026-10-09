<?php 

namespace App\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StreamService
{
    protected $url;

    public function data()
    {
        try {
            $url = config('services.stream.metadata');

            if (!$url) {
                Log::warning('Radio API error: STREAM_METADATA is not configured in .env');
                return $this->fallbackData();
            }

            $response = Http::timeout(5)->withOptions([
                'verify' => false,
            ])->get($url);
            
            if ($response->failed()) {
                Log::warning('Radio API returned error status: ' . $response->status());
                return $this->fallbackData();
            }

            $data = $response->json();

            if (!is_array($data)) {
                Log::warning('Radio API returned unexpected data format');
                return $this->fallbackData();
            }

            return [
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
        } catch (\Throwable $e) {
            Log::error('Radio API error: ' . $e->getMessage());
            return $this->fallbackData();
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
