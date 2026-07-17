<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\OnairFilter;

use App\Http\Controllers\Controller;

use App\Http\Resources\Onair\OnairResource;

use App\Services\External\AudienceService;

use Inertia\Inertia;

class LogsPageController extends Controller
{
    private $render = 'private/Logs';

    public function __construct(
        private AudienceService $audienceService,
        private OnairFilter $onairFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'audience' => $this->audienceService->getAudience(),
            'onair' => OnairResource::collection(
                $this->onairFilter->apply([
                    'execution_modes' => ['live', 'scheduled'],
                    'with' => ['program.host'],
                    'paginate' => 10,
                ])
            ),
        ]);
    }
}
