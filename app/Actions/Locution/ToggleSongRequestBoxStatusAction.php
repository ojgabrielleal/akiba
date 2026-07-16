<?php

namespace App\Actions\Locution;

use App\Models\Onair;
use Illuminate\Support\Facades\DB;

class ToggleSongRequestBoxStatusAction
{
    public function execute(): ?Onair
    {
        return DB::transaction(function () {
            $onair = Onair::live()->first();

            if (!$onair) {
                return null;
            }

            $onair->update([
                'allows_song_requests' => !$onair->allows_song_requests,
            ]);

            return $onair;
        });
    }
}
