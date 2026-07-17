<?php

namespace App\Actions\SongRequest;

use App\Models\Music;
use App\Models\Onair;
use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class StoreSongRequestAction
{
    public function execute(array $data, string $ipAddress): SongRequest
    {
        return DB::transaction(function () use ($data, $ipAddress) {
            $onair = Onair::live()->firstOrFail();
            $music = Music::where('name', $data['music']['name'])->first();
        
            if (! $music) {
                $music = Music::create([
                    'production' => $data['music']['production'],
                    'type' => $data['music']['type'],
                    'artist' => $data['music']['artist'],
                    'name' => $data['music']['name'],
                    'image' => $data['music']['image'],
                ]);
            } else {
                $music->increment('song_requests_total');
            }

            return $onair->songRequests()->create([
                'ip_address' => $ipAddress,
                'name' => $data['name'],
                'address' => $data['address'],
                'message' => $data['message'],
                'music_id' => $music->id,
            ]);
        });
    }
}
