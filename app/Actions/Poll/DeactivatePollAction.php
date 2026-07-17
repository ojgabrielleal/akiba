<?php

namespace App\Actions\Poll;

use App\Models\Poll;

use Illuminate\Support\Facades\DB;

class DeactivatePollAction
{
    public function execute(Poll $poll): Poll
    {
        return DB::transaction(function () use ($poll) {
            $poll->update(['is_active' => false]);

            return $poll;
        });
    }
}
