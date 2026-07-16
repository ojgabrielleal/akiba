<?php

namespace App\Http\Controllers\Private\Radio\Music;

use App\Actions\Music\RefreshMusicRankingAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Music;

class RefreshMusicRankingController extends Controller
{
    use HasFlashMessages;

    public function __invoke(RefreshMusicRankingAction $refreshMusicRankingAction)
    {
        $this->authorize('setRanking', Music::class);

        $refreshMusicRankingAction->execute();

        return $this->flashMessage('update');
    }
}
