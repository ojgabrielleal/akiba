<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormats;
use App\Models\RadioAudienceSnapshot;
use App\Models\RadioStation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudienceResource extends JsonResource
{
    use HasFormats;

    private const COLORS = [
        '#ff8500',
        '#0091ff',
        '#a8f000',
        '#b832f5',
        '#ffffff',
        '#ff3b3b',
        '#00d4c8',
        '#ffd000',
        '#ff5eaa',
        '#7d8cff',
        '#74d680',
        '#ff9f68',
        '#c8b6ff',
        '#4dd8ff',
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $snapshot = $this->latestAudienceSnapshot;

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'logo' => $this->logo,
            'website' => $this->website,
            'endpoint' => $this->endpoint,
            'listeners_path' => $this->listeners_path,
            'is_active' => $this->is_active,
            'listeners' => $snapshot?->listeners,
            'status' => $snapshot?->status ?? 'offline',
            'response_time_ms' => $snapshot?->response_time_ms,
            'collected_at' => $snapshot?->created_at?->toISOString(),
        ];
    }

    public static function toCollectionArray(Collection $collection, Request $request, string $format,): ?array 
    {
        if ($format !== 'history') {
            return null;
        }

        $period = self::period($request->string('audience_period')->toString());
        $buckets = self::buckets($period);
        $allStations = $collection
            ->map(fn ($item) => $item->resource ?? $item);
        $akiba = $allStations->firstWhere('name', 'Rádio Akiba');
        $stations = $allStations
            ->reject(fn (RadioStation $station) => $station->is($akiba))
            ->sortByDesc(fn (RadioStation $station) => $station->audienceSnapshots->avg('listeners') ?? -1)
            ->take($akiba ? 4 : 5)
            ->concat([$akiba])
            ->filter()
            ->sortByDesc(fn (RadioStation $station) => $station->audienceSnapshots->avg('listeners') ?? -1)
            ->values();

        return [
            'period' => $period,
            'labels' => $buckets->pluck('label')->all(),
            'series' => $stations
                ->map(function (RadioStation $station, int $index) use ($buckets, $period): array {
                    $measurements = $station->audienceSnapshots
                        ->groupBy(fn (RadioAudienceSnapshot $snapshot) => self::bucketKey(
                            Carbon::instance($snapshot->created_at),
                            $period,
                        ));

                    return [
                        'uuid' => $station->uuid,
                        'name' => $station->name,
                        'logo' => $station->logo,
                        'color' => self::COLORS[$index % count(self::COLORS)],
                        'data' => $buckets
                            ->map(function (array $bucket) use ($measurements, $period): ?int {
                                $values = $measurements->get($bucket['key']);

                                return $values?->isNotEmpty()
                                    ? ($period === 'week'
                                        ? (int) $values->max('listeners')
                                        : (int) round($values->avg('listeners')))
                                    : null;
                            })
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private static function period(string $period): string
    {
        return in_array($period, ['day', 'week', 'month', 'semester'], true)
            ? $period
            : 'day';
    }

    private static function buckets(string $period): Collection
    {
        [$start, $interval] = match ($period) {
            'week' => [now()->subWeek()->startOfHour(), '6 hours'],
            'month' => [now()->subMonth()->startOfDay(), '1 day'],
            'semester' => [now()->subMonths(6)->startOfWeek(), '1 week'],
            default => [now()->subDay()->startOfHour(), '1 hour'],
        };

        return collect(CarbonPeriod::create($start, $interval, now()))
            ->filter(fn (Carbon $date): bool => $period !== 'week' || in_array($date->hour, [0, 12, 18], true))
            ->map(fn (Carbon $date): array => [
                'key' => self::bucketKey($date, $period),
                'label' => self::bucketLabel($date, $period),
            ]);
    }

    private static function bucketKey(Carbon $date, string $period): string
    {
        return match ($period) {
            'week' => sprintf('%s %02d:00', $date->format('Y-m-d'), self::weekPartHour($date->hour)),
            'month' => $date->format('Y-m-d'),
            'semester' => $date->copy()->startOfWeek()->format('Y-m-d'),
            default => $date->format('Y-m-d H:00'),
        };
    }

    private static function bucketLabel(Carbon $date, string $period): string
    {
        return match ($period) {
            'week' => sprintf('%s %s', $date->format('d/m'), self::weekPartLabel($date->hour)),
            'month' => $date->format('d/m'),
            'semester' => $date->format('d/m'),
            default => $date->format('H\\h'),
        };
    }

    private static function weekPartHour(int $hour): int
    {
        return $hour < 12 ? 0 : ($hour < 18 ? 12 : 18);
    }

    private static function weekPartLabel(int $hour): string
    {
        return $hour < 12 ? 'Dia' : ($hour < 18 ? 'Tarde' : 'Noite');
    }
}
