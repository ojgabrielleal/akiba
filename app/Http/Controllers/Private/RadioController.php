<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\ListenerMonth;
use App\Models\Music;
use App\Models\Program;
use App\Models\User;

use App\Http\Resources\ListenerMonthResource;
use App\Http\Resources\MusicResource;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\UserResource;

use App\Actions\Radio\CreateListenerMonthAction;
use App\Actions\Program\CreateProgramAction;
use App\Actions\Radio\GenerateMusicRankingAction;
use App\Actions\Radio\UpdateMusicAction;
use App\Actions\Radio\UpdateMusicRankingAction;
use App\Actions\Program\UpdateProgramAction;

use App\Http\Requests\Radio\CreateProgramRequest;
use App\Http\Requests\Radio\UpdateMusicRequest;
use DomainException;

class RadioController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Radio';

    /*
     * ======================
     * USERS
     * ======================
     */

    public function indexUsers()
    {
        if (request()->user()->cannot('viewAny', User::class)) {
            return null;
        }

        return UserResource::collection(User::get())->format('compact');
    }

    /*
     * ======================
     * PROGRAMS
     * ======================
     */
    public function indexPrograms()
    {
        if (request()->user()->cannot('list', Program::class)) {
            return null;
        }

        return ProgramResource::collection(
            Program::with([
                    'host', 
                    'airtimes', 
                    'plans' => fn ($query) => $query->unexecuted()->orderBy('scheduled_at')
                ])
                ->active()
                ->get()
        )->format('grouped_by_execution_mode');
    }

    public function showProgram(Program $program)
    {
        if (request()->user()->cannot('view', $program)) {
            return null;
        }

        return new ProgramResource(
            $program->load([
                'host',
                'airtimes',
                'plans' => fn ($query) => $query->unexecuted()->orderBy('scheduled_at'),
                'plans.user',
            ])
        );
    }

    public function createProgram(CreateProgramRequest $request, CreateProgramAction $createProgramAction)
    {
        if ($request->user()->cannot('create', Program::class)) {
            return null;
        }

        try {
            $createProgramAction->execute(
                $request->user(),
                $request->all(),
                $request->file('image')
            );

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function updateProgram(Request $request, UpdateProgramAction $updateProgramAction, Program $program)
    {
        if ($request->user()->cannot('update', $program)) {
            return null;
        }

        try {
            $updateProgramAction->execute(
                $program,
                $request->user(),
                $request->all(),
                $request->file('image')
            );

            return $this->flashMessage('update');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function deactivateProgram(Program $program)
    {
        if (request()->user()->cannot('delete', $program)) {
            return null;
        }

        $program->update(['is_active' => false]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * MUSIC
     * ======================
     */

    public function indexMusicRanking()
    {
        if (request()->user()->cannot('list', Music::class)) {
            return null;
        }

        return MusicResource::collection(Music::mostRequested());
    }

    public function updateMusicRanking(Request $request, Music $music, UpdateMusicRankingAction $updateMusicRankingAction)
    {
        if ($request->user()->cannot('update', $music)) {
            return null;
        }

        $updateMusicRankingAction->execute(
            $music,
            $request->file('image_ranking')
        );

        return $this->flashMessage('update');
    }

    public function updateMusic(UpdateMusicRequest $request, UpdateMusicAction $updateMusicAction, Music $music)
    {
        if ($request->user()->cannot('update', $music)) {
            return null;
        }

        $updateMusicAction->execute(
            $music,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }

    public function generateMusicRanking(GenerateMusicRankingAction $generateMusicRankingAction)
    {
        if (request()->user()->cannot('setRanking', Music::class)) {
            return null;
        }

        $generateMusicRankingAction->execute();

        return $this->flashMessage('update');
    }

    /*
     * ======================
     * LISTENER MONTH
     * ======================
     */

    public function showListenerMonth()
    {
        if (request()->user()->cannot('listener.month.view')) {
            return null;
        }

        $listener = ListenerMonth::first();

        return $listener ? new ListenerMonthResource($listener) : null;
    }

    public function showListenerMonthFound()
    {
        if (request()->user()->cannot('listener.month.view')) {
            return null;
        }

        $listener = ListenerMonth::mostActiveListenerOfCurrentMonth();

        return $listener ? new ListenerMonthResource($listener) : null;
    }

    public function createListenerMonth(Request $request, CreateListenerMonthAction $createListenerMonthAction)
    {
        if ($request->user()->cannot('listener.month.set')) {
            return null;
        }

        $createListenerMonthAction->execute(
            $request->file('avatar')
        );

        return $this->flashMessage('save');
    }

    /*
     * ======================
     *  RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'users' => $this->indexUsers(),
            'programs' => $this->indexPrograms(),
            'musicRanking' => $this->indexMusicRanking(),
            'listenerMonth' => $this->showListenerMonth(),
        ]);
    }
}
