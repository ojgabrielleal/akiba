<?php

namespace App\Actions\Locution;

use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class MarkSongRequestAsPlayedAction
{
    public function execute(SongRequest $songRequest): SongRequest
    {
        return DB::transaction(function () use ($songRequest) {
            $songRequest->update(['was_reproduced' => true]);
            $songRequest->onair()->increment('song_requests_total');

            return $songRequest;
        });
    }
}
