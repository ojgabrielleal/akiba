<?php

namespace App\Http\Controllers\Private;

use App\Services\MusicService;
use App\Services\ProgramService;
use App\Services\UserService;

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
use App\Services\ListenerMonthService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\ListenerMonth\StoreListenerMonthRequest;
use App\Http\Requests\Music\UpdateMusicRequest;
use App\Http\Requests\Program\StoreProgramRequest;
use App\Http\Requests\Program\UpdateProgramRequest;
use DomainException;

class RadioController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Radio';

    public function __construct(
        private MusicService $musicFilter,
        private ProgramService $programFilter,
        private UserService $userFilter,
    ) {}

    private function indexUsers()
    {
        return $this->whenCanViewAny(User::class,
            fn () => UserResource::collection(
                $this->userFilter->filter()
            ),
        );
    }

    private function indexPrograms()
    {
        return $this->whenCanViewAny(Program::class,
            fn () => ProgramResource::collection(
                $this->programFilter->filter([
                    'with' => $this->programRelations(),
                    'active' => true,
                ])
            )->format('grouped'),
        );
    }

    private function indexRanking()
    {
        return $this->whenCanViewAny(Music::class,
            fn () => MusicResource::collection(
                $this->musicFilter->filter([
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

    private function programRelations(): array
    {
        return [
            'host',
            'programAirtimes',
            'schedules' => fn ($query) => $query->pendingExecution()->orderBy('scheduled_at'),
        ];
    }

    public function storeProgram(StoreProgramRequest $request, ProgramService $service)
    {
        try {
            $service->store($request->user(), $this->responsible($request), $request->validated(), $request->file('image'));

            return $this->flashMessage('save');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function updateProgram(UpdateProgramRequest $request, ProgramService $service, Program $program)
    {
        try {
            $service->update($program, $request->user(), $this->responsible($request), $request->validated(), $request->file('image'));

            return $this->flashMessage('update');
        } catch (DomainException $exception) {
            return $this->flashMessage('error', $exception->getMessage());
        }
    }

    public function deactivateProgram(ProgramService $service, Program $program)
    {
        $this->authorize('deactivate', $program);

        $service->deactivate($program);

        return $this->flashMessage('deactivate');
    }

    public function updateMusic(UpdateMusicRequest $request, MusicService $service, Music $music)
    {
        $service->update($music, $request->validated(), $request->file('image'), $request->file('image_ranking'));

        return $this->flashMessage('update');
    }

    public function refreshMusicRanking(MusicService $service)
    {
        $this->authorize('refreshRanking', Music::class);

        $service->refreshMusicRanking();

        return $this->flashMessage('update');
    }

    public function storeListenerMonth(StoreListenerMonthRequest $request, ListenerMonthService $service)
    {
        $service->store();

        return $this->flashMessage('save');
    }

    private function responsible(StoreProgramRequest|UpdateProgramRequest $request): User
    {
        $requiresResponsible = in_array($request->input('execution_mode'), ['auto_dj', 'scheduled', 'playlist'], true)
            || (
                $request->input('execution_mode') === 'live'
                && $request->input('access_type') === 'private'
            );

        if (! $requiresResponsible) {
            return $request->user();
        }

        return User::where('uuid', $request->input('user'))->firstOrFail();
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'users' => $this->indexUsers(),
            'programs' => $this->indexPrograms(),
            'ranking' => $this->indexRanking(),
            'listenerMonth' => $this->indexListenerMonth(),
        ]);
    }
}
