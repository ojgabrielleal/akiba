<?php

namespace App\Actions\Poll;

use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StorePollVoteAction
{
    public function execute(PollOption $option, User $user): PollVote
    {
        return DB::transaction(fn () => $option->poll->votes()->firstOrCreate(
            ['user_id' => $user->id],
            ['poll_option_id' => $option->id],
        ));
    }
}
