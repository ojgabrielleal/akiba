<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\AudienceFilter;
use App\Filters\OnairFilter;

use App\Http\Controllers\Controller;

use App\Http\Resources\AudienceResource;
use App\Http\Resources\Onair\OnairResource;

use Inertia\Inertia;

class ReportsPageController extends Controller
{
    private $render = 'private/Reports';

    public function __construct(
        private AudienceFilter $audienceFilter,
        private OnairFilter $onairFilter,
    ) {}

    public function render()
    {
        $audiencePeriod = request()->string('audience_period')->toString();
        $audiencePeriod = in_array($audiencePeriod, ['day', 'week', 'month', 'semester'], true)
            ? $audiencePeriod
            : 'day';

        return Inertia::render($this->render, [
            'audience' => AudienceResource::collection(
                $this->audienceFilter->apply([
                    'active' => true,
                    'with' => 'latestAudienceSnapshot',
                    'order_by_audience' => true,
                ])
            ),
            'audienceHistory' => fn () => AudienceResource::collection(
                $this->audienceFilter->history($audiencePeriod)
            )->format('history'),
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
