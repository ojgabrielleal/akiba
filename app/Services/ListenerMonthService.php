<?php

namespace App\Services;

use App\Models\ListenerMonth;
use App\Models\OAuthAccount;
use Illuminate\Support\Facades\DB;

class ListenerMonthService
{
    public function store()
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
                'top_anime' => null,
                'requests_total' => $found->requests_total,
            ]);
        });
    }

    public function updateTopAnime(ListenerMonth $listenerMonth, OAuthAccount $oauthAccount, array $topAnime): ListenerMonth
    {
        abort_unless($listenerMonth->oauth_account_id === $oauthAccount->id, 403);

        $oauthAccount->update([
            'top_anime' => $topAnime,
        ]);

        $listenerMonth->update([
            'top_anime' => $topAnime,
        ]);

        return $listenerMonth;
    }
}
