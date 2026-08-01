<?php

namespace App\Actions\Poll;

use App\Models\PollOption;
use App\Models\PollVote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StorePollVoteAction
{
    public function execute(PollOption $option, Model $voter): PollVote
    {
        return DB::transaction(function () use ($option, $voter) {
            $vote = $option->poll->votes()->firstOrNew([
                'voter_type' => $voter->getMorphClass(),
                'voter_id' => $voter->getKey(),
            ]);

            if (!$vote->exists) {
                $vote->poll_option_id = $option->id;
                $vote->save();
            }

            return $vote;
        });
    }
}
