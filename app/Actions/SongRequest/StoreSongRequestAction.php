<?php

namespace App\Actions\SongRequest;

use App\Models\Music;
use App\Models\OAuthAccount;
use App\Models\Onair;
use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class StoreSongRequestAction
{
    public function execute(array $data, OAuthAccount $oauthAccount): SongRequest
    {
        return DB::transaction(function () use ($data, $oauthAccount) {
            $onair = Onair::acceptingSongRequests()->firstOrFail();
            $music = Music::where('name', $data['music']['name'])->first();

            if (array_key_exists('address', $data)) {
                $oauthAccount->update([
                    'address' => $data['address'],
                    'profile_completed_at' => now(),
                ]);
            }

            if (!$music) {
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
                'oauth_account_id' => $oauthAccount->id,
                'music_id' => $music->id,
                'message' => $data['message'],
            ]);
        });
    }
}
