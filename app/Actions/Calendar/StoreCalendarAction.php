<?php

namespace App\Actions\Calendar;

use Carbon\Carbon;

use App\Models\User;
use App\Models\Calendar;
use Illuminate\Support\Facades\DB;

class StoreCalendarAction
{
    public function execute(User $user, array $data): Calendar
    {
        return DB::transaction(fn () => Calendar::create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'hour' => $data['hour'],
            'type' => $data['type'],
            'date' => $data['date'],
            'day_of_week' => Carbon::parse($data['date'])->dayOfWeek,
        ]));
    }
}
