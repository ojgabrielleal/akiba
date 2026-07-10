<?php

namespace App\Http\Controllers\Private\Radio\Music;

use App\Actions\Radio\Music\GenerateMusicRankingAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Music;

class GenerateMusicRankingController extends Controller
{
    use HasFlashMessages;

    public function __invoke(GenerateMusicRankingAction $generateMusicRankingAction)
    {
        $this->authorize('setRanking', Music::class);

        $generateMusicRankingAction->execute();

        return $this->flashMessage('update');
    }
}
