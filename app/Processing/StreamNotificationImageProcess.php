<?php

namespace App\Processing;

use GdImage;
use RuntimeException;

class StreamNotificationImageProcess
{
    public function render(string $program, string $nickname): string
    {
        $source = imagecreatefrompng(resource_path('images/discord/stream-background.png'));
        if (! $source) {
            throw new RuntimeException('Unable to load stream notification background.');
        }

        $image = imagecreatetruecolor(1536, 1024);
        try {
            imagecopyresampled($image, $source, 0, 0, 0, 0, 1536, 1024, imagesx($source), imagesy($source));
            // Keep the artwork visible while guaranteeing contrast behind variable text.
            for ($x = 0; $x < 1000; $x++) {
                $alpha = (int) round(30 + 97 * ($x / 999));
                imageline($image, $x, 0, $x, 1023, imagecolorallocatealpha($image, 9, 14, 23, $alpha));
            }

            $white = imagecolorallocate($image, 248, 249, 252);
            $orange = imagecolorallocate($image, 255, 170, 53);
            $muted = imagecolorallocate($image, 193, 205, 219);
            $dark = imagecolorallocate($image, 13, 19, 28);

            $this->text($image, 'RÁDIO AKIBA', 30, 72, 108, $white);
            imagefilledrectangle($image, 72, 162, 334, 224, $orange);
            imagefilledellipse($image, 100, 193, 16, 16, $dark);
            $this->text($image, 'AO VIVO', 24, 125, 205, $dark);

            $program = $this->normalize($program) ?: 'Programa ao vivo';
            $size = 64;
            do {
                $lines = $this->wrap($program, $size, 650);
                if (count($lines) <= 3 || $size <= 40) {
                    break;
                }
                $size -= 4;
            } while (true);
            if (count($lines) > 3) {
                $lines = array_slice($lines, 0, 3);
                $lines[2] = $this->fit($lines[2].'…', $size, 650);
            }
            foreach ($lines as $index => $line) {
                $this->text($image, $line, $size, 72, 340 + $index * ($size + 25), $white);
            }

            $this->text($image, 'NO COMANDO', 19, 76, 654, $muted);
            $host = $this->normalize($nickname) ?: 'Akiba';
            $this->text($image, $this->fit('DJ '.$host, 35, 650), 35, 72, 715, $orange);
            imagefilledrectangle($image, 72, 768, 170, 772, $orange);
            $this->text($image, 'Sintonize agora', 24, 72, 828, $white);
            $this->text($image, 'akiba.com.br', 20, 72, 872, $muted);

            ob_start();
            try {
                imagejpeg($image, null, 90);

                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        } finally {
            imagedestroy($source);
            imagedestroy($image);
        }
    }

    private function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    private function text(GdImage $image, string $text, int $size, int $x, int $y, int $color): void
    {
        imagettftext($image, $size, 0, $x, $y, $color, $this->font(), $text);
    }

    private function font(): string
    {
        return resource_path('fonts/DejaVuSans-Bold.ttf');
    }

    private function width(string $text, int $size): int
    {
        $box = imagettfbbox($size, 0, $this->font(), $text);

        return max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]);
    }

    private function fit(string $text, int $size, int $width): string
    {
        if ($this->width($text, $size) <= $width) {
            return $text;
        }

        return rtrim($this->prefix($text, $size, $width - $this->width('…', $size))).'…';
    }

    private function prefix(string $text, int $size, int $width): string
    {
        $low = 0;
        $high = mb_strlen($text);
        while ($low < $high) {
            $middle = (int) ceil(($low + $high) / 2);
            if ($this->width(mb_substr($text, 0, $middle), $size) <= $width) {
                $low = $middle;
            } else {
                $high = $middle - 1;
            }
        }

        return mb_substr($text, 0, $low);
    }

    private function wrap(string $text, int $size, int $width): array
    {
        $lines = [];
        $line = '';
        foreach (explode(' ', $text) as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if ($line !== '' && $this->width($candidate, $size) > $width) {
                $lines[] = $line;
                $line = '';
            }
            // Split unusually long words without letting them overlap the presenter.
            while ($this->width($word, $size) > $width) {
                $part = $this->prefix($word, $size, $width);
                $lines[] = $part;
                $word = mb_substr($word, mb_strlen($part));
            }
            $line = $line === '' ? $word : $line.' '.$word;
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
