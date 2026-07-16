<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use App\Services\External\AudienceService;
use Inertia\Inertia;

use App\Models\Onair;

use App\Http\Resources\Onair\OnairResource;

class LogsPageController extends Controller
{
    private $audience;

    private $render = 'private/Logs';

    public function __construct(AudienceService $audience)
    {
        $this->audience = $audience;
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'audience' => $this->audience->getAudience(),
            'onair' => $this->indexOnair(),
        ]);
    }

    public function indexOnair()
    {
        return OnairResource::collection(
            Onair::where('execution_mode', 'live')
                ->orWhere('execution_mode', 'scheduled')
                ->with(['program.host'])
                ->latest()
                ->paginate(10)
        );
    }
}
