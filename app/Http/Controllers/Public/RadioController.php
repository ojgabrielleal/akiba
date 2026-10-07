<?php

namespace App\Http\Controllers\Public;

use App\Services\MusicService;
use App\Services\ListenerMonthService;
use App\Services\ProgramService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListenerMonth\UpdateListenerMonthTopAnimeRequest;
use App\Http\Resources\ListenerMonthResource;
use App\Http\Resources\MusicResource;
use App\Http\Resources\Program\ProgramResource;
use App\Models\ListenerMonth;
use App\Models\OAuthAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RadioController extends Controller
{
    public function __construct(
        private MusicService $musicFilter,
        private ProgramService $programFilter,
    ) {}

    private function indexPrograms(Request $request)
    {
        return ProgramResource::collection(
            $this->programFilter->filter([
                'with' => [
                    'host',
                    'programAirtimes' => fn ($query) => $query->orderBy('day')->orderBy('hour'),
                    'schedules' => fn ($query) => $query->pendingExecution()->orderBy('scheduled_at'),
                ],
                'active' => true,
                'execution_mode' => 'live',
                'public_schedule' => true,
            ])
        );
    }

    private function indexRanking()
    {
        return MusicResource::collection(
            $this->musicFilter->filter([
                'order_by' => 'song_requests_total',
                'order_direction' => 'desc',
                'limit' => 12,
            ])
        );
    }

    private function indexListenerMonth(): array
    {
        $listenerMonth = ListenerMonth::first();

        return [
            'current' => $listenerMonth ? new ListenerMonthResource($listenerMonth) : null,
        ];
    }

    public function updateListenerMonthTopAnime(UpdateListenerMonthTopAnimeRequest $request, ListenerMonthService $service, ListenerMonth $listenerMonth)
    {
        $oauthAccount = $request->attributes->get('oauth_account');

        abort_unless($oauthAccount instanceof OAuthAccount, 403);

        $service->updateTopAnime($listenerMonth, $oauthAccount, $request->validated('top_anime'));

        return $this->flashMessage('update');
    }

    public function render(Request $request)
    {
        return Inertia::render('public/Radio', [
            'programs' => $this->indexPrograms($request),
            'ranking' => $this->indexRanking(),
            'listenerMonth' => $this->indexListenerMonth(),
            'about' => null,
        ]);
    }
}
