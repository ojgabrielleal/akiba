<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnimeThemeService
{
    private $baseUrl = 'https://api.animethemes.moe';

    public function search(string $query)
    {
        $query = mb_strtolower(trim($query));

        if ($query === '') return collect();

        $cacheKey = 'animethemes.musics.v2.' . md5($query);

        if (Cache::has($cacheKey)) return Cache::get($cacheKey);

        $response = Http::timeout(5)->withOptions([
            'verify' => false,
        ])->get("{$this->baseUrl}/search", [
            'q' => $query,
            'include' => [
                'animetheme' => 'anime.images,song.artists',
            ],
        ]);

        if ($response->failed()) {
            Log::warning('AnimeTheme API is not service' . $response->status());
            return null;
        }

        $data = $response->json();

        $formatted = collect($data['search']['animethemes'] ?? [])
            ->filter(fn ($item) => isset($item['anime']))
            ->groupBy('anime.id')
            ->map(function ($themes) {
                $anime = $themes->first()['anime'];

                return [
                    'anime' => $anime['name'],
                    'banner' => $anime['images'][0]['link'] ?? null,
                    'musics' => $themes->map(function ($music) {
                        return [
                            'type' => $music['type'],
                            'title' => $music['song']['title'] ?? null,
                            'artists' => collect($music['song']['artists'] ?? [])->pluck('name')->join(', ')
                        ];
                    })->values(),
                ];
            })
            ->values();

        Cache::put($cacheKey, $formatted, now()->addHours(12));

        return $formatted;
    }
}
