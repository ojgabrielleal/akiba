<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormats;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RepositoryResource extends JsonResource
{
    use HasFormats;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'image' => $this->image, 
            'url' => $this->url, 
            'type' => $this->type,
        ];
    }

    public static function toCollectionArray(Collection $collection, Request $request, ?string $format): ?array
    {
        if ($format !== 'grouped') {
            return null;
        }

        return [
            'tutorial' => self::resolveTypeModeCollection($collection, $request, 'tutorial'),
            'package' => self::resolveTypeModeCollection($collection, $request, 'package'),
            'software' => self::resolveTypeModeCollection($collection, $request, 'software'),
        ];
    }

    private static function resolveTypeModeCollection(Collection $collection, Request $request, string $type): array
    {
        return $collection
            ->filter(fn ($item) => $item->type === $type)
            ->values()
            ->map(fn ($item) => self::make($item->resource ?? $item)->resolve($request))
            ->all();
    }

}
