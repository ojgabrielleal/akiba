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
        $response = Http::timeout(5)->withOptions([
            'verify' => false,
        ])->get("{$this->baseUrl}/anime", [
            'q'=> $name,
            'include' => 'animethemes.song.artists,images'
        ]);

        if($response->failed()){
            Log::warning('AnimeTheme API is not service' . $response->status());
            return null;
        }

        $data = $response->json();

        $formatted = collect($data['anime'])->map(function($item){
            return [
                'anime' => $item['name'],
                'banner' => $item['images'][0]['link'],
                'musics' => collect($item['animethemes'])->map(function($music){
                    return [
                        'type' => $music['type'],
                        'title' => $music['song']['title'],
                        'artists' => collect($music['song']['artists'])->pluck('name')->join(', ')
                    ];
                })
            ];
        });

        return $formatted;
    }
}