<?php

namespace App\Http\Controllers\Private;

use App\Services\OnairService;
use App\Services\ProgramService;
use App\Services\SongRequestService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\SongRequestResource;

use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;

use Inertia\Inertia;
use App\Services\LocutionService;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Locution\StartLocutionRequest;

class LocutionController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Locution';

    public function __construct(
        private OnairService $onairFilter,
        private ProgramService $programFilter,
        private SongRequestService $songRequestFilter,
    ) {}

    private function getOnair(): ?Onair
    {
        return $this->onairFilter->filter([
            'live' => true,
            'with' => 'program.host',
            'first' => true,
        ]);
    }

    private function indexPrograms()
    {
        return $this->whenCanViewAny(Program::class,
            fn () => ProgramResource::collection(
                $this->programFilter->filter([
                    'available_for_locution' => request()->user(),
                ])
            ),
        );
    }

    private function indexOnair(?Onair $onair): ?OnairResource
    {
        return $onair ? new OnairResource($onair) : null;
    }

    private function indexSongRequests(?Onair $onair)
    {
        return $this->whenCanViewAny(SongRequest::class,
            fn () => SongRequestResource::collection(
                $this->songRequestFilter->filter([
                    'onair_id' => $onair?->id,
                    'order_by' => 'created_at',
                    'order_direction' => 'asc',
                ])
            ),
        );
    }

    public function startLocution(StartLocutionRequest $request, LocutionService $service, Program $program)
    {
        $service->start($request->user(), $program, $request->validated());

        return $this->flashMessage('start');
    }

    public function finishLocution(LocutionService $service)
    {
        $this->authorize('locution.finish');

        $service->finish();

        return $this->flashMessage('finish');
    }

    public function markSongRequestAsPlayed(LocutionService $service, SongRequest $songRequest)
    {
        $this->authorize('markAsPlayed', $songRequest);

        $service->markSongRequestAsPlayed($songRequest);

        return $this->flashMessage('complete');
    }

    public function markSongRequestAsCanceled(LocutionService $service, SongRequest $songRequest)
    {
        $this->authorize('markAsCanceled', $songRequest);

        $service->markSongRequestAsCanceled($songRequest);

        return $this->flashMessage('update');
    }

    public function toggleSongRequestBoxStatus(LocutionService $service)
    {
        $this->authorize('toggleBoxStatus', SongRequest::class);

        $onair = $service->toggleSongRequestBoxStatus();

        if (!$onair) {
            return $this->flashMessage('error');
        }

        return $this->flashMessage('save');
    }

    public function render()
    {
        $onair = $this->getOnair();

        return Inertia::render($this->render, [
            'programs' => $this->indexPrograms(),
            'onair' => $this->indexOnair($onair),
            'songRequests' => $this->indexSongRequests($onair),
        ]);
    }
}
