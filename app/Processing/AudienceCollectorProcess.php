<?php

namespace App\Processing;

use App\Models\Onair;
use App\Models\RadioStation;
use App\Integrations\AudienceService;
use Illuminate\Support\Facades\Log;

class AudienceCollectorProcess
{
    public string $internalStationName;

    public function __construct(private AudienceService $audienceService)
    {
        $this->internalStationName = (string) config(
            'services.audience.internal_station_name'
        );
    }

    public function collect(): array
    {
        $summary = [
            'stations' => 0,
            'online' => 0,
            'offline' => 0,
            'invalid_response' => 0,
            'slow' => 0,
        ];
        $slowResponseMs = (int) config('services.audience.slow_response_ms', 1500);

        RadioStation::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->lazyById()
            ->each(function (RadioStation $radioStation) use (&$summary, $slowResponseMs): void {
                $measurement = $this->audienceService->get($radioStation);
                $radioStation->audienceSnapshots()->create($measurement);

                $summary['stations']++;
                $summary[$measurement['status']] = ($summary[$measurement['status']] ?? 0) + 1;

                if (($measurement['response_time_ms'] ?? 0) >= $slowResponseMs) {
                    $summary['slow']++;

                    Log::info('Audience collection slow response.', [
                        'station' => $radioStation->name,
                        'endpoint' => $radioStation->endpoint,
                        'response_time_ms' => $measurement['response_time_ms'],
                        'status' => $measurement['status'],
                    ]);
                }

                if ($radioStation->name === $this->internalStationName) {
                    $this->updateCurrentProgramPeak($measurement);
                }
            });

        return $summary;
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
