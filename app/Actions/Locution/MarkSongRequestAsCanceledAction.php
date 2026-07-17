<?php

namespace App\Actions\Locution;

use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class MarkSongRequestAsCanceledAction
{
    public function execute(SongRequest $songRequest): SongRequest
    {
        return DB::transaction(function () use ($songRequest) {
            $songRequest->update(['was_canceled' => true]);
            $songRequest->onair()->decrement('song_requests_total');

            return $songRequest;
        });
    }
}
