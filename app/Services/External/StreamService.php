<?php 

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StreamService
{
    protected $url;

    public function stream()
    {
        return redirect()->to(config('services.stream.url'));
    }

    public function data()
    {
        try {
            $url = config('services.stream.metadata');

            if (!$url) {
                Log::warning('Radio API error: STREAM_METADATA is not configured in .env');
                return null;
            }

            $response = Http::timeout(5)->withOptions([
                'verify' => false,
            ])->get($url);
            
            if ($response->failed()) {
                Log::warning('Radio API returned error status: ' . $response->status());
                return null;
            }

            $data = $response->json();

            if (!isset($data['status'])) {
                Log::warning('Radio API returned unexpected data format');
                return null;
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
            return null;
        }
    }
}
