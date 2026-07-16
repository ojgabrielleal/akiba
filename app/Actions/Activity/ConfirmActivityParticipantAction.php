<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConfirmActivityParticipantAction
{
    public function execute(Activity $activity, User $user): void
    {
        DB::transaction(fn () => $activity->confirmations()->attach($user->id));
    }
}
