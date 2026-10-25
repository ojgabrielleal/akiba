<?php

namespace App\Processing;

final readonly class SocialArtworkResult
{
    public function __construct(
        public string $template,
        public string $path,
        public string $url,
        public string $mimeType,
        public int $width,
        public int $height,
        public int $size,
    ) {}
}
