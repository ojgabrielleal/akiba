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
                    'music' => $data['musica_atual'] ?? 'Desconhecido',
                    'cover' => $data['capa_musica'] ?? null,
                ]
            ];
        } catch (\Throwable $e) {
            Log::error('Radio API error: ' . $e->getMessage());
            return $this->fallbackData();
        }
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
        ];
    }
}
