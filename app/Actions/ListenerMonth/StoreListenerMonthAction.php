<?php

namespace App\Actions\ListenerMonth;

use App\Models\ListenerMonth;

use Illuminate\Support\Facades\DB;

class StoreListenerMonthAction
{
    public function execute()
    {
        return DB::transaction(function () {
            $found = ListenerMonth::mostActiveListenerOfCurrentMonth();

            if (!$found) {
                return null;
            }

            return ListenerMonth::first()->update([
                'oauth_account_id' => $found->oauth_account_id,
                'favorite_program' => $found->favorite_program,
                'favorite_music' => $found->favorite_music,
                'requests_total' => $found->requests_total,
            ]);
        });
    }
}
