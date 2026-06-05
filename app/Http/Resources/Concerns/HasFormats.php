<?php

namespace App\Http\Resources\Concerns;

use App\Http\Resources\FormattableResourceCollection;

trait HasFormats
{
    private ?string $format = null;

    public static function collection($resource): FormattableResourceCollection
    {
        return new FormattableResourceCollection($resource, static::class);
    }

    public function format(?string $format): static
    {
        $this->format = $format;

        return $this;
    }
}
