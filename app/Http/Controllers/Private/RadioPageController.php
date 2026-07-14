<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\ListenerMonth;
use App\Models\Music;
use App\Models\Program;
use App\Models\User;

use App\Http\Resources\ListenerMonthResource;
use App\Http\Resources\MusicResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;

class RadioPageController extends Controller
{
    private $render = 'private/Radio';

    public function render()
    {
        return Inertia::render($this->render, [
            'users' => $this->indexUsers(),
            'programs' => $this->indexPrograms(),
            'ranking' => $this->indexRanking(),
            'listenerMonth' => [
                'current' => $this->currentListenerMonth(),
                'found' => $this->foundListenerMonth(),
            ],
        ]);
    }

    public function indexUsers()
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::get())->format('compact');
    }
    
    public function indexPrograms()
    {
        $this->authorize('viewAny', Program::class);

        return ProgramResource::collection(
            Program::with([
                    'host', 
                    'programAirtimes', 
                    'plans' => fn ($query) => $query->unexecuted()->orderBy('scheduled_at')
                ])
                ->active()
                ->get()
        )->format('grouped_by_execution_mode');
    }

    public function indexRanking()
    {
        $this->authorize('viewAny', Music::class);

        return MusicResource::collection(Music::mostRequested());
    }

    public function currentListenerMonth()
    {
        $this->authorize('listener.month.view');

        $listener = ListenerMonth::first();

        return $listener ? new ListenerMonthResource($listener) : null;
    }

    public function foundListenerMonth()
    {
        $this->authorize('listener.month.view');

        return ListenerMonth::mostActiveListenerOfCurrentMonth();
    }
}
