<?php

namespace App\Actions\Radio;

use Illuminate\Support\Facades\DB;
use App\Models\Music;

class GenerateMusicRankingAction
{
    public function execute(): void
    {
        DB::transaction(function () {
            Music::ranking()->update([
                'in_ranking' => false
            ]);
            
            Music::orderBy('song_requests_total', 'desc')->limit(10)->update([
                'in_ranking' => true,
            ]);
        });
    }
}
