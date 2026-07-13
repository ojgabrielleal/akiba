<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProgramResource extends JsonResource
{
    use HasFormats;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'image' => $this->image,
            'access_type' => $this->access_type,
            'execution_mode' => $this->execution_mode,
            'is_default_auto_dj' => $this->is_default_auto_dj,
            'phrases' => $this->phrases ?? [],
            'host' => UserResource::make($this->host)->format('summary'),
            'airtimes' => ProgramAirtimeResource::collection($this->programAirtimes),
            'plans' => PlanResource::collection($this->plans)->format('summary'),
        ];
    }

    public static function toCollectionArray(Collection $collection, Request $request, ?string $format): ?array
    {
        if ($format !== 'grouped_by_execution_mode') {
            return null;
        }

        return [
            'live' => self::resolveExecutionModeCollection($collection, $request, 'live'),
            'scheduled' => self::resolveExecutionModeCollection($collection, $request, 'scheduled'),
            'playlist' => self::resolveExecutionModeCollection($collection, $request, 'playlist'),
            'auto_dj' => self::resolveExecutionModeCollection($collection, $request, 'auto_dj'),
        ];
    }

    private static function resolveExecutionModeCollection(Collection $collection, Request $request, string $executionMode): array
    {
        return $collection
            ->filter(fn ($item) => $item->execution_mode === $executionMode)
            ->values()
            ->map(fn ($item) => self::make($item->resource ?? $item)->resolve($request))
            ->all();
    }
}
