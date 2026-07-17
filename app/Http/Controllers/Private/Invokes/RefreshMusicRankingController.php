<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Music\RefreshMusicRankingAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Music;

class RefreshMusicRankingController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private RefreshMusicRankingAction $refreshMusicRankingAction,
    ) {}

    public function __invoke()
    {
        $this->authorize('refreshRanking', Music::class);

        $this->refreshMusicRankingAction->execute();

        return $this->flashMessage('update');
    }
}
