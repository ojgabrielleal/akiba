<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class FormattableResourceCollection extends AnonymousResourceCollection
{
    private ?string $format = null;

    public function format(?string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function toArray(Request $request): array
    {
        if ($this->format && is_string($this->collects) && method_exists($this->collects, 'toCollectionArray')) {
            $formatted = $this->collects::toCollectionArray($this->collection, $request, $this->format);

            if ($formatted !== null) {
                return $formatted;
            }
        }

        return $this->collection
            ->map(fn (JsonResource $resource) => $resource->format($this->format)->resolve($request))
            ->all();
    }
}
