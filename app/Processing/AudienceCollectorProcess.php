<?php

namespace App\Processing;

use App\Models\Onair;
use App\Models\RadioStation;
use App\Integrations\AudienceService;

class AudienceCollectorProcess
{
    public string $internalStationName;

    public function __construct(private AudienceService $audienceService)
    {
        $this->internalStationName = (string) config(
            'services.audience.internal_station_name'
        );
    }

    public function collect(): void
    {
        RadioStation::query()
            ->where('is_active', true)
            ->get()
            ->each(function (RadioStation $radioStation): void {
                $measurement = $this->audienceService->get($radioStation);
                $radioStation->audienceSnapshots()->create($measurement);

                if ($radioStation->name === $this->internalStationName) {
                    $this->updateCurrentProgramPeak($measurement);
                }
            });
    }

    private function updateCurrentProgramPeak(array $measurement): void
    {
        if ($measurement['status'] !== 'online' || $measurement['listeners'] === null) {
            return;
        }

        $onair = Onair::live()
            ->whereIn('execution_mode', ['live', 'scheduled'])
            ->latest('id')
            ->first();

        if (! $onair) {
            return;
        }

        Onair::query()
            ->whereKey($onair->id)
            ->where('peak_listeners', '<', $measurement['listeners'])
            ->update([
                'peak_listeners' => $measurement['listeners'],
                'peak_listeners_at' => now(),
            ]);
    }
}
