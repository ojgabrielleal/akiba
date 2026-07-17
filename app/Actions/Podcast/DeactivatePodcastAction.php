<?php

namespace App\Actions\Podcast;

use App\Models\Podcast;

use Illuminate\Support\Facades\DB;

class DeactivatePodcastAction
{
    public function execute(Podcast $podcast): Podcast
    {
        return DB::transaction(function () use ($podcast) {
            $podcast->update(['is_active' => false]);

            return $podcast;
        });
    }
}
