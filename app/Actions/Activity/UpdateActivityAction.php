<?php

namespace App\Actions\Administration\Activity;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Activity;

class UpdateActivityAction
{
    public function execute(Activity $activity, User $user, array $data): Activity
    {
        return DB::transaction(function () use ($activity, $user, $data) {
            $confirmationsAllowed = $data['purpose'] === 'activity';

            $activity->fill([
                'title' => $data['title'],
                'limit' => $data['limit'],
                'content' => $data['content'],
                'allows_confirmations' => $confirmationsAllowed,
            ]);

            if ($activity->isDirty()) {
                $activity->save();
            }

            if ($confirmationsAllowed) {
                $activity->calendar()->updateOrCreate(
                    ['activity_id' => $activity->id],
                    [
                        'user_id' => $user->id,
                        'content' => $data['title'],
                        'hour' => $data['hour'],
                        'date' => $data['date'],
                        'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
                    ]
                );
            }

            return $activity;
        });
    }
}
