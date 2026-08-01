<?php

namespace App\Http\Controllers\Public\Pages;

use App\Filters\MusicFilter;
use App\Filters\ProgramFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListenerMonthResource;
use App\Http\Resources\MusicResource;
use App\Http\Resources\Program\ProgramResource;
use App\Models\ListenerMonth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RadioPageController extends Controller
{
    public function __construct(
        private MusicFilter $musicFilter,
        private ProgramFilter $programFilter,
    ) {}

    public function render(Request $request)
    {
        return Inertia::render('public/Radio', [
            'programs' => $this->indexPrograms($request),
            'activeProgramMode' => $this->resolveProgramMode($request),
            'ranking' => $this->indexRanking(),
            'listenerMonth' => $this->indexListenerMonth(),
            'about' => null,
        ]);
    }

    private function indexPrograms(Request $request)
    {
        return ProgramResource::collection(
            $this->programFilter->apply([
                'with' => [
                    'host',
                    'programAirtimes',
                    'schedules' => fn ($query) => $query->pendingExecution()->orderBy('scheduled_at'),
                ],
                'active' => true,
                'execution_mode' => $this->resolveProgramMode($request),
                'public_schedule' => $this->resolveProgramMode($request) === 'live',
                'paginate' => 8,
            ])
        );
    }

    private function indexRanking()
    {
        return MusicResource::collection(
            $this->musicFilter->apply([
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

    private function resolveProgramMode(Request $request): string
    {
        $mode = $request->query('program_mode', 'live');

        return in_array($mode, ['live', 'scheduled', 'playlist'], true)
            ? $mode
            : 'live';
    }
}
