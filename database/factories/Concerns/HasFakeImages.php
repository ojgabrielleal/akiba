<?php

namespace Database\Factories\Concerns;

trait HasFakeImages
{
    protected function fakeImageUrl(int $width = 640, int $height = 480): string
    {
        return sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            rawurlencode(fake()->uuid()),
            $width,
            $height,
        );
    }
}
