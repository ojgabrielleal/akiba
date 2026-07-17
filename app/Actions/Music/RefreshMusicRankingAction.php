<?php

namespace App\Actions\Music;

use App\Models\Music;

use Illuminate\Support\Facades\DB;

class RefreshMusicRankingAction
{
    public function execute(): void
    {
        DB::transaction(function () {
            Music::inRanking()->update([
                'in_ranking' => false
            ]);

            Music::orderBy('song_requests_total', 'desc')->limit(10)->update([
                'in_ranking' => true,
            ]);
        });
    }
}
