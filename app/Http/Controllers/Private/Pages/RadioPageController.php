<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\MusicFilter;
use App\Filters\ProgramFilter;
use App\Filters\UserFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\ListenerMonthResource;
use App\Http\Resources\MusicResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\User\UserResource;

use App\Models\ListenerMonth;
use App\Models\Music;
use App\Models\Program;
use App\Models\User;

use Inertia\Inertia;

class RadioPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Radio';

    public function __construct(
        private MusicFilter $musicFilter,
        private ProgramFilter $programFilter,
        private UserFilter $userFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'users' => $this->indexUsers(),
            'programs' => $this->indexPrograms(),
            'ranking' => $this->indexRanking(),
            'listenerMonth' => $this->indexListenerMonth(),
        ]);
    }

    private function indexUsers()
    {
        return $this->whenCanViewAny(User::class,
            fn () => UserResource::collection(
                $this->userFilter->apply()
            ),
        );
    }

    private function indexPrograms()
    {
        return $this->whenCanViewAny(Program::class,
            fn () => ProgramResource::collection(
                $this->programFilter->apply([
                    'with' => [
                        'host',
                        'programAirtimes',
                        'schedules' => fn ($query) => $query->pendingExecution()->orderBy('scheduled_at'),
                    ],
                    'active' => true,
                ])
            )->format('grouped'),
        );
    }

    private function indexRanking()
    {
        return $this->whenCanViewAny(Music::class,
            fn () => MusicResource::collection(
                $this->musicFilter->apply([
                    'order_by' => 'song_requests_total',
                    'order_direction' => 'desc',
                    'limit' => 3,
                ])
            ),
        );
    }

    private function indexListenerMonth()
    {
        return $this->whenCanViewAny(ListenerMonth::class,
            function () {
                $listenerMonth = ListenerMonth::first();

                return [
                    'current' => $listenerMonth ? new ListenerMonthResource($listenerMonth) : null,
                    'found' => ListenerMonth::mostActiveListenerOfCurrentMonth(),
                ];
            },
        );
    }
}
