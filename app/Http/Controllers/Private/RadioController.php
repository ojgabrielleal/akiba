<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
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

use App\Actions\Radio\ListenerMonth\CreateListenerMonthAction;
use App\Actions\Radio\Program\CreateProgramAction;
use App\Actions\Radio\Music\GenerateMusicRankingAction;
use App\Actions\Radio\Music\UpdateMusicAction;
use App\Actions\Radio\Program\UpdateProgramAction;

use App\Http\Requests\Web\Radio\CreateListenerMonthRequest;
use App\Http\Requests\Web\Radio\CreateProgramRequest;
use App\Http\Requests\Web\Radio\GenerateMusicRankingRequest;
use App\Http\Requests\Web\Radio\UpdateMusicRequest;
use App\Http\Requests\Web\Radio\UpdateProgramRequest;
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
        try {
            $createProgramAction->execute(
                $request->user(),
                $request->validated(),
                $request->file('image')
            );

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function updateProgram(UpdateProgramRequest $request, UpdateProgramAction $updateProgramAction, Program $program)
    {
        try {
            $updateProgramAction->execute(
                $program,
                $request->user(),
                $request->validated(),
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

    public function indexRanking()
    {
        if (request()->user()->cannot('list', Music::class)) {
            return null;
        }

        return MusicResource::collection(Music::mostRequested());
    }

    public function updateMusic(UpdateMusicRequest $request, UpdateMusicAction $updateMusicAction, Music $music)
    {
        $updateMusicAction->execute(
            $music,
            $request->validated(),
            $request->file('image'),
            $request->file('image_ranking')
        );

        return $this->flashMessage('update');
    }

    public function generateMusicRanking(GenerateMusicRankingRequest $request, GenerateMusicRankingAction $generateMusicRankingAction)
    {
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
        return new ListenerMonthResource($listener);
    }

    public function showListenerMonthFound()
    {
        if (request()->user()->cannot('listener.month.view')) {
            return null;
        }

        return ListenerMonth::mostActiveListenerOfCurrentMonth();
    }

    public function createListenerMonth(CreateListenerMonthRequest $request, CreateListenerMonthAction $createListenerMonthAction)
    {
        $createListenerMonthAction->execute(
            $request->validated(),
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
            'ranking' => $this->indexRanking(),
            'listenerMonth' => [
                'current' => $this->showListenerMonth(),
                'found' => $this->showListenerMonthFound(),
            ],
        ]);
    }
}
