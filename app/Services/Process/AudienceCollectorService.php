<?php

namespace App\Services\Process;

use App\Models\RadioStation;
use App\Services\External\AudienceService;

class AudienceCollectorService
{
    public function __construct(
        private AudienceService $audienceService,
    ) {}

    public function collect(): void
    {
        RadioStation::query()
            ->where('is_active', true)
            ->get()
            ->each(function (RadioStation $radioStation): void {
                $measurement = $this->audienceService->get($radioStation);
                $radioStation->audienceSnapshots()->create($measurement);
            });
    }
}
