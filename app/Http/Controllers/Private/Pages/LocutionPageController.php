<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\OnairFilter;
use App\Filters\ProgramFilter;
use App\Filters\SongRequestFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\Program\ProgramResource;
use App\Http\Resources\SongRequestResource;

use App\Models\Program;
use App\Models\SongRequest;

use Inertia\Inertia;

class LocutionPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Locution';

    public function __construct(
        private OnairFilter $onairFilter,
        private ProgramFilter $programFilter,
        private SongRequestFilter $songRequestFilter,
    ) {}

    public function render()
    {
        $onair = $this->indexOnair();

        return Inertia::render($this->render, [
            'programs' => $this->indexPrograms(),
            'onair' => $this->showOnair($onair),
            'songRequests' => $this->indexSongRequests($onair?->id),
        ]);
    }

    private function indexPrograms()
    {
        return $this->whenCanViewAny(Program::class,
            fn () => ProgramResource::collection(
                $this->programFilter->apply([
                    'available_for_locution' => request()->user(),
                ])
            ),
        );
    }

    private function indexOnair()
    {
        return $this->onairFilter->apply([
            'live' => true,
            'with' => 'program.host',
            'first' => true,
        ]);
    }

    private function showOnair($onair): ?OnairResource
    {
        return $onair ? new OnairResource($onair) : null;
    }

    private function indexSongRequests(?int $onairId)
    {
        return $this->whenCanViewAny(SongRequest::class,
            fn () => SongRequestResource::collection(
                $this->songRequestFilter->apply([
                    'onair_id' => $onairId,
                    'order_by' => 'created_at',
                    'order_direction' => 'asc',
                ])
            ),
        );
    }
}
