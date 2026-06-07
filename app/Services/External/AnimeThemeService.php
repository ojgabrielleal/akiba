<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnimeThemeService
{
    private $baseUrl = 'https://api.animethemes.moe';

    public function getMusics($name = null)
    {
        $nameQuery = mb_strtolower(trim((string) $name));
        $cacheKey = 'animethemes.musics.' . md5($nameQuery);

        if (Cache::has($cacheKey)) return Cache::get($cacheKey);

        $response = Http::timeout(5)->withOptions([
            'verify' => false,
        ])->get("{$this->baseUrl}/anime", [
            'q'=> $nameQuery,
            'include' => 'animethemes.song.artists,images'
        ]);

        if ($response->failed()) {
            Log::warning('AnimeTheme API is not service' . $response->status());
            return null;
        }

        $data = $response->json();

        $formatted = collect($data['anime'] ?? [])->map(function ($item) {
            return [
                'anime' => $item['name'],
                'banner' => $item['images'][0]['link'] ?? null,
                'musics' => collect($item['animethemes'] ?? [])->map(function ($music) {
                    return [
                        'type' => $music['type'],
                        'title' => $music['song']['title'] ?? null,
                        'artists' => collect($music['song']['artists'] ?? [])->pluck('name')->join(', ')
                    ];
                })->values()
            ];
        })->values();

        Cache::put($cacheKey, $formatted, now()->addHours(12));

        return $formatted;
    }
}
