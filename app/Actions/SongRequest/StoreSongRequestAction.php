<?php

namespace App\Actions\SongRequest;

use App\Models\Music;
use App\Models\OAuthAccount;
use App\Models\Onair;
use App\Models\SongRequest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StoreSongRequestAction
{
    public function execute(array $data, Model $requester): SongRequest
    {
        return DB::transaction(function () use ($data, $requester) {
            $onair = $this->acceptingSongRequestsOnair();
            $music = $this->music($data['music']);

            if ($requester instanceof OAuthAccount) {
                $this->completeOAuthAccountProfile($requester, $data);
            }

            $songRequest = $onair->songRequests()->make([
                'music_id' => $music->id,
                'message' => $data['message'],
            ]);

            $songRequest->requester()->associate($requester);
            $songRequest->save();

            return $songRequest;
        });
    }

    private function acceptingSongRequestsOnair(): Onair
    {
        return Onair::acceptingSongRequests()->firstOrFail();
    }

    private function music(array $data): Music
    {
        $music = Music::where('name', $data['name'])->first();

        if (! $music) {
            return Music::create([
                'production' => $data['production'],
                'type' => $data['type'],
                'artist' => $data['artist'],
                'name' => $data['name'],
                'image' => $data['image'],
            ]);
        }

        $music->increment('song_requests_total');

        return $music;
    }

    private function completeOAuthAccountProfile(OAuthAccount $oauthAccount, array $data): void
    {
        if (empty($data['address']) || empty($data['birth_date'])) {
            return;
        }

        $oauthAccount->update([
            'address' => $data['address'],
            'birth_date' => $data['birth_date'],
            'profile_completed_at' => now(),
        ]);
    }
}
