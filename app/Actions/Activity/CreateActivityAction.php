<?php

namespace App\Actions\Administration\Activity;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Activity;

class CreateActivityAction
{
    public function execute(User $user, array $data): Activity
    {
        return DB::transaction(function () use ($user, $data) {
            $mayHaveConfirmations = $data['purpose'] === 'activity';

            $activity = Activity::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'limit' => $data['limit'],
                'content' => $data['content'],
                'allows_confirmations' => $mayHaveConfirmations,
            ]);

            if ($mayHaveConfirmations) {
                $activity->calendar()->create([
                    'user_id' => $user->id,
                    'content' => $data['title'],
                    'hour' => $data['hour'],
                    'date' => $data['date'],
                    'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
                ]);
            }

            return $activity;
        });
    }
}
