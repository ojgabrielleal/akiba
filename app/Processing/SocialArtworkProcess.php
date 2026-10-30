<?php

namespace App\Processing;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\PolygonFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use InvalidArgumentException;
use RuntimeException;

class SocialArtworkProcess
{
    private const FORMATS = [
        'instagram-feed' => [1080, 1350],
        'instagram-live-feed' => [1080, 1350],
        'instagram-story' => [1080, 1920],
        'instagram-live-story' => [1080, 1920],
        'facebook-feed' => [1200, 630],
        'facebook-live-feed' => [1200, 630],
        'discord-post' => [1200, 630],
    ];

    private const MAX_FILE_SIZE = 8_000_000;

    public function __construct(
        private ?ImageManager $manager = null,
        private ?string $fontPath = null,
        private ?string $brandLogoPath = null,
    ) {
        $this->manager ??= new ImageManager(new Driver);
        $this->fontPath ??= resource_path('fonts/NotoSans-ExtraBold.ttf');
        $this->brandLogoPath ??= public_path('img/brand/logo.webp');
    }

    public function generate(
        string $template,
        array $data,
        string $postUuid,
        string $publicationUuid,
        string $format = 'jpeg',
        int $quality = 88,
    ): SocialArtworkResult {
        $contents = $this->render($template, $data, $format, $quality);
        [$width, $height, $mimeType] = $this->inspect($contents);
        $extension = $format === 'webp' ? 'webp' : 'jpg';
        $path = implode('/', [
            'social-artworks',
            $this->pathSegment($postUuid),
            $this->pathSegment($publicationUuid),
            $template.'-'.Str::uuid().'.'.$extension,
        ]);

        if (! Storage::disk('public')->put($path, $contents)) {
            throw new RuntimeException("Unable to store social artwork [{$path}].");
        }

        $storageUrl = Storage::disk('public')->url($path);

        return new SocialArtworkResult(
            template: $template,
            path: $path,
            url: Str::startsWith($storageUrl, ['http://', 'https://']) ? $storageUrl : url($storageUrl),
            mimeType: $mimeType,
            width: $width,
            height: $height,
            size: strlen($contents),
        );
    }

    public function render(string $template, array $data, string $format = 'jpeg', int $quality = 88): string
    {
        [$width, $height] = self::FORMATS[$template]
            ?? throw new InvalidArgumentException("Unsupported social artwork template [{$template}].");

        if ($quality < 1 || $quality > 100) {
            throw new InvalidArgumentException('Social artwork quality must be between 1 and 100.');
        }

        $image = $this->manager->create($width, $height)->fill('#000014');

        if (str_starts_with($template, 'instagram-live-')) {
            $this->drawLiveArtwork($image, $data, $template, $width, $height);
        } else {
            $cover = $this->readImage($data['cover'] ?? null, public_path('img/placeholders/placeholder.webp'))
                ->cover($width, $height, 'center');
            $image->place($cover);
            $this->drawOverlay($image, $width, $height, $template);
            $this->drawFrame($image, $width, $height, $template);
            $this->drawBrand($image, $template, $width, $height);
            $this->drawEditorialContent($image, $data, $template, $width, $height);
        }

        $contents = match ($format) {
            'jpeg', 'jpg' => (string) $image->toJpeg(quality: $quality, progressive: true),
            'webp' => (string) $image->toWebp(quality: $quality),
            default => throw new InvalidArgumentException("Unsupported social artwork output format [{$format}]."),
        };

        [$actualWidth, $actualHeight] = $this->inspect($contents);

        if ([$actualWidth, $actualHeight] !== [$width, $height]) {
            throw new RuntimeException(
                "Generated social artwork has invalid dimensions [{$actualWidth}x{$actualHeight}].",
            );
        }

        return $contents;
    }

    private function drawInstagramEditorialArtwork(
        ImageInterface $image,
        array $data,
        int $width,
        int $height,
    ): void {
        $image->fill('#fffaf3');

        $cover = $this->readImage($data['cover'] ?? null, public_path('img/placeholders/placeholder.webp'))
            ->cover($width - 80, 740, 'center');
        $image->place($cover, 'top-left', 40, 40);
        $image->drawRectangle(40, 40, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width - 80, 740)
            ->background('rgba(0, 0, 20, 0.12)'));

        $image->drawRectangle(0, 0, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width, $height)
            ->background('rgba(0, 0, 0, 0)')
            ->border('#0091ff', 18));
        $image->drawRectangle(18, 18, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width - 36, $height - 36)
            ->background('rgba(0, 0, 0, 0)')
            ->border('#ffaa35', 8));
        $image->drawRectangle(40, 780, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width - 80, 14)
            ->background('#ffaa35'));

        $category = 'NOVIDADES';
        $date = Str::upper($data['published_at'] ?? now('America/Sao_Paulo')->format('d M Y'));
        $categoryText = $this->italicText($category, 17, '#fffaf3');
        $categoryWidth = min(420, max(190, $categoryText->width() + 44));
        $categoryY = 825;
        $image->drawRectangle(70, $categoryY, fn (RectangleFactory $rectangle) => $rectangle
            ->size($categoryWidth, 54)
            ->background('#ffaa35'));
        $image->place($categoryText, 'top-left', 92, $categoryY + intdiv(54 - $categoryText->height(), 2));
        $image->text($date, $width - 70, 845, fn (FontFactory $font) => $this->font(
            $font,
            23,
            '#002080',
        )->align('right'));

        [$title, $titleSize] = $this->fitTitle(
            $data['title'] ?? 'Conteúdo novo na Rede Akiba',
            $width - 140,
        );
        $titleLines = substr_count($title, "\n") + 1;
        $titleHeight = (int) ceil($titleLines * $titleSize * 1.56);
        $titleY = 920 + max(0, intdiv(250 - $titleHeight, 2));
        $image->text($title, 70, $titleY, fn (FontFactory $font) => $this->font(
            $font,
            $titleSize,
            '#002080',
            1.56,
        ));

        $footerY = $height - 112;
        $image->drawRectangle(70, $footerY - 22, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width - 140, 3)
            ->background('#0091ff'));
        $avatar = $this->readImage($data['author_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
            ->cover(64, 64, 'top');
        $avatar = $this->circularImage($avatar, 64);
        $image->place($avatar, 'top-left', 70, $footerY);
        $image->text(
            $data['author'] ?? 'Rede Akiba',
            156,
            $footerY + 32,
            fn (FontFactory $font) => $this->font($font, 25, '#002080')->valign('middle'),
        );
        $logo = $this->manager
            ->read($this->requiredAsset($this->brandLogoPath, 'brand logo'))
            ->scaleDown(width: 180, height: 64);
        $logo = $this->removeLightBackground($logo);
        $image->place(
            $logo,
            'top-left',
            $width - 70 - $logo->width(),
            $footerY + intdiv(64 - $logo->height(), 2),
        );
    }

    /** @return array{0: string, 1: int} */
    private function fitTitle(string $title, int $maxWidth): array
    {
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? $title);

        for ($size = 62; $size >= 34; $size -= 2) {
            $lines = $this->wrap($title, $size, $maxWidth, 4);

            if (! str_ends_with(end($lines), '…')) {
                return [implode("\n", $lines), $size];
            }
        }

        return [implode("\n", $this->wrap($title, 34, $maxWidth, 4)), 34];
    }

    private function circularImage(ImageInterface $image, int $size): ImageInterface
    {
        $source = imagecreatefromstring((string) $image->toPng());
        $circle = imagecreatetruecolor($size, $size);
        imagealphablending($circle, false);
        imagesavealpha($circle, true);
        $transparent = imagecolorallocatealpha($circle, 0, 0, 0, 127);
        imagefill($circle, 0, 0, $transparent);
        imagecopy($circle, $source, 0, 0, 0, 0, $size, $size);

        $radius = $size / 2;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ((($x - $radius) ** 2) + (($y - $radius) ** 2) > $radius ** 2) {
                    imagesetpixel($circle, $x, $y, $transparent);
                }
            }
        }

        ob_start();
        imagepng($circle);
        $contents = (string) ob_get_clean();
        imagedestroy($source);
        imagedestroy($circle);

        return $this->manager->read($contents);
    }

    private function halftonePattern(int $width, int $height, string $color): ImageInterface
    {
        $pattern = imagecreatetruecolor($width, $height);
        imagealphablending($pattern, false);
        imagesavealpha($pattern, true);
        $transparent = imagecolorallocatealpha($pattern, 0, 0, 0, 127);
        imagefill($pattern, 0, 0, $transparent);
        [$red, $green, $blue] = sscanf(ltrim($color, '#'), '%02x%02x%02x');
        $dot = imagecolorallocatealpha($pattern, $red, $green, $blue, 88);
        $spacing = 30;

        for ($y = 0; $y < $height; $y += $spacing) {
            for ($x = 0; $x < $width; $x += $spacing) {
                $step = (($x / $spacing) + ($y / $spacing)) % 3;
                $diameter = 5 + ((int) $step * 2);
                $offset = (($y / $spacing) % 2) * intdiv($spacing, 2);
                imagefilledellipse($pattern, $x + $offset, $y, $diameter, $diameter, $dot);
            }
        }

        ob_start();
        imagepng($pattern);
        $contents = (string) ob_get_clean();
        imagedestroy($pattern);

        return $this->manager->read($contents);
    }

    private function italicText(string $text, int $size, string $color): ImageInterface
    {
        $font = $this->requiredAsset($this->fontPath, 'font');
        $box = imagettfbbox($size, 0, $font, $text);
        $minX = min($box[0], $box[2], $box[4], $box[6]);
        $maxX = max($box[0], $box[2], $box[4], $box[6]);
        $minY = min($box[1], $box[3], $box[5], $box[7]);
        $maxY = max($box[1], $box[3], $box[5], $box[7]);
        $padding = 4;
        $width = $maxX - $minX + ($padding * 2);
        $height = $maxY - $minY + ($padding * 2);
        $slant = (int) ceil($height * 0.22);
        $source = imagecreatetruecolor($width, $height);
        $italic = imagecreatetruecolor($width + $slant, $height);

        foreach ([$source, $italic] as $canvas) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        }

        [$red, $green, $blue] = sscanf(ltrim($color, '#'), '%02x%02x%02x');
        imagealphablending($source, true);
        imagettftext(
            $source,
            $size,
            0,
            $padding - $minX,
            $padding - $minY,
            imagecolorallocate($source, $red, $green, $blue),
            $font,
            $text,
        );

        imagealphablending($italic, true);
        for ($y = 0; $y < $height; $y++) {
            $offset = (int) round(($height - $y) * 0.22);
            imagecopy($italic, $source, $offset, $y, 0, $y, $width, 1);
        }

        ob_start();
        imagepng($italic);
        $contents = (string) ob_get_clean();
        imagedestroy($source);
        imagedestroy($italic);

        return $this->manager->read($contents);
    }

    private function removeLightBackground(ImageInterface $image): ImageInterface
    {
        $resource = imagecreatefromstring((string) $image->toPng());
        imagealphablending($resource, false);
        imagesavealpha($resource, true);
        $transparent = imagecolorallocatealpha($resource, 0, 0, 0, 127);

        for ($y = 0; $y < imagesy($resource); $y++) {
            for ($x = 0; $x < imagesx($resource); $x++) {
                $color = imagecolorat($resource, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;

                if ($red >= 238 && $green >= 235 && $blue >= 228) {
                    imagesetpixel($resource, $x, $y, $transparent);
                }
            }
        }

        ob_start();
        imagepng($resource);
        $contents = (string) ob_get_clean();
        imagedestroy($resource);

        return $this->manager->read($contents);
    }

    private function drawOverlay(ImageInterface $image, int $width, int $height, string $template): void
    {
        $usesDiscordComposition = in_array($template, ['discord-post', 'instagram-feed', 'facebook-feed'], true);
        $baseOpacity = $usesDiscordComposition ? .10 : .26;
        $image->drawRectangle(0, 0, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width, $height)
            ->background(sprintf('rgba(0, 0, 20, %.2f)', $baseOpacity)));

        if ($usesDiscordComposition) {
            $gradientTop = (int) ($height * .36);
            $gradientHeight = (int) ($height * .39);

            for ($offset = 0; $offset < $gradientHeight; $offset += 2) {
                $progress = $offset / $gradientHeight;
                $opacity = .76 * ($progress ** 1.7);
                $image->drawRectangle(0, $gradientTop + $offset, fn (RectangleFactory $rectangle) => $rectangle
                    ->size($width, 2)
                    ->background(sprintf('rgba(0, 0, 20, %.3f)', $opacity)));
            }

            $image->drawRectangle(0, $gradientTop + $gradientHeight, fn (RectangleFactory $rectangle) => $rectangle
                ->size($width, $height - $gradientTop - $gradientHeight)
                ->background('rgba(0, 0, 20, 0.88)'));

            return;
        }

        $image->drawRectangle(0, (int) ($height * .43), fn (RectangleFactory $rectangle) => $rectangle
            ->size($width, (int) ($height * .57))
            ->background('rgba(0, 0, 20, 0.78)'));
    }

    private function drawFrame(ImageInterface $image, int $width, int $height, string $template): void
    {
        if (in_array($template, ['discord-post', 'instagram-feed', 'facebook-feed'], true)) {
            return;
        }

        $safe = str_contains($template, 'story') ? 80 : 48;

        $image->drawRectangle(intdiv($safe, 2), intdiv($safe, 2), fn (RectangleFactory $rectangle) => $rectangle
            ->size($width - $safe, $height - $safe)
            ->background('rgba(0, 0, 0, 0)')
            ->border('#0091ff', 10));
    }

    private function drawBrand(ImageInterface $image, string $template, int $width, int $height): void
    {
        $isStory = str_contains($template, 'story');
        $isDiscord = $template === 'discord-post';
        $isFacebookFeed = $template === 'facebook-feed';
        $usesDiscordComposition = $isDiscord || $template === 'instagram-feed' || $isFacebookFeed;
        $logo = $this->manager
            ->read($this->requiredAsset($this->brandLogoPath, 'brand logo'))
            ->scaleDown(width: $isStory ? 260 : 230, height: 90);

        $image->place(
            $logo,
            'top-left',
            $usesDiscordComposition ? $width - 42 - $logo->width() : 72,
            $isStory ? 250 : ($isFacebookFeed ? 40 : ($usesDiscordComposition ? $height - 36 - $logo->height() : 72)),
        );
    }

    private function drawEditorialContent(
        ImageInterface $image,
        array $data,
        string $template,
        int $width,
        int $height,
    ): void {
        $isStory = $template === 'instagram-story';
        $isDiscord = $template === 'discord-post';
        $isInstagramFeed = $template === 'instagram-feed';
        $isFacebookFeed = $template === 'facebook-feed';
        $usesDiscordComposition = $isDiscord || $isInstagramFeed || $isFacebookFeed;
        $left = $isStory ? 80 : ($isDiscord ? 48 : 54);
        $bottom = $isStory ? 280 : ($isDiscord ? 48 : 82);
        $titleSize = $isStory ? 66 : ($isDiscord ? 44 : ($isFacebookFeed ? 48 : 64));
        $maxTextWidth = $width - ($left * 2);
        $footerY = $height - $bottom;
        $titleY = $isDiscord ? 455 : ($isFacebookFeed ? 350 : ($isInstagramFeed ? 900 : $footerY - ($isStory ? 470 : 390)));
        $category = Str::upper($data['category'] ?? 'Notícia');
        $date = Str::upper($data['published_at'] ?? now('America/Sao_Paulo')->format('d M Y'));

        $metadata = $usesDiscordComposition ? $date : "{$category}  ·  {$date}";
        $image->text($metadata, $left, $titleY - ($usesDiscordComposition ? 40 : 54), fn (FontFactory $font) => $this->font(
            $font,
            $isDiscord ? 19 : ($isInstagramFeed ? 23 : ($isFacebookFeed ? 20 : 24)),
            '#ffaa35',
        ));

        $title = $data['titles'][$template] ?? $data['title'] ?? 'Conteúdo novo na Rede Akiba';
        $maxTitleLines = $isInstagramFeed ? 4 : (($isDiscord || $isFacebookFeed) ? 3 : 4);
        $title = implode("\n", $this->wrap($title, $titleSize, $maxTextWidth, $maxTitleLines));
        $image->text($title, $left, $titleY, fn (FontFactory $font) => $this->font(
            $font,
            $titleSize,
            '#fffaf3',
            $isDiscord ? 1.50 : ($isInstagramFeed ? 1.55 : ($isFacebookFeed ? 1.25 : 1.16)),
        ));

        if ($isDiscord || $isFacebookFeed) {
            return;
        }

        if ($isInstagramFeed) {
            $avatar = $this->readImage($data['author_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
                ->cover(75, 75, 'top')
                ->crop(60, 60, 7, 0);
            $avatar = $this->circularImage($avatar, 60);
            $authorY = $height - 110;
            $image->place($avatar, 'top-left', $left, $authorY);
            $author = $data['author'] ?? 'Rede Akiba';
            $authorX = $left + 78;
            $image->text(
                $author,
                $authorX,
                $authorY + 20,
                fn (FontFactory $font) => $this->font($font, 22, '#fffaf3')->valign('middle'),
            );
            $image->text(
                'Redação Akiba',
                $authorX,
                $authorY + 45,
                fn (FontFactory $font) => $this->font($font, 15, '#ffaa35')->valign('middle'),
            );

            return;
        }

        $avatar = $this->readImage($data['author_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
            ->cover(72, 72, 'top');
        $image->place($avatar, 'top-left', $left, $footerY - 72);
        $image->text(
            $data['author'] ?? 'Rede Akiba',
            $left + 96,
            $footerY - 35,
            fn (FontFactory $font) => $this->font($font, 27, '#fffaf3'),
        );
    }

    private function drawLiveArtwork(
        ImageInterface $image,
        array $data,
        string $template,
        int $width,
        int $height,
    ): void {
        $isStory = $template === 'instagram-live-story';
        $live = $data['live'] ?? [];

        if ($template === 'facebook-live-feed') {
            $this->drawFacebookLiveArtwork($image, $live, $width, $height);

            return;
        }

        if (! $isStory) {
            $this->drawLiveFeedArtwork($image, $live, $width, $height);

            return;
        }

        $image->fill('#002080');
        $image->drawRectangle(0, 0, fn (RectangleFactory $rectangle) => $rectangle
            ->size($width, $height)
            ->background('rgba(0, 0, 20, 0.28)'));
        $this->drawFrame($image, $width, $height, $template);
        $this->drawBrand($image, $template, $width, $height);

        $portraitSize = $isStory ? 560 : 430;
        $portraitX = $isStory ? intdiv($width - $portraitSize, 2) : 72;
        $portraitY = $isStory ? 470 : 360;
        $portrait = $this->readImage($live['host_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
            ->cover($portraitSize, $portraitSize, 'top');

        $image->drawRectangle($portraitX - 12, $portraitY - 12, fn (RectangleFactory $rectangle) => $rectangle
            ->size($portraitSize + 24, $portraitSize + 24)
            ->background('#ffaa35'));
        $image->place($portrait, 'top-left', $portraitX, $portraitY);

        $textX = $isStory ? intdiv($width, 2) : 555;
        $textY = $isStory ? 1110 : 500;
        $alignment = $isStory ? 'center' : 'left';
        $headlineSize = $isStory ? 104 : 78;
        $image->text('AGORA', $textX, $textY, fn (FontFactory $font) => $this->font(
            $font,
            $headlineSize,
            '#fffaf3',
        )->align($alignment));

        $boxWidth = $isStory ? 470 : 380;
        $boxX = $isStory ? intdiv($width - $boxWidth, 2) : $textX;
        $boxY = $textY + $headlineSize + 28;
        $image->drawRectangle($boxX, $boxY, fn (RectangleFactory $rectangle) => $rectangle
            ->size($boxWidth, $headlineSize + 38)
            ->background('#ffaa35'));
        $image->text('NO AR', $isStory ? intdiv($width, 2) : $boxX + 20, $boxY + 10, fn (FontFactory $font) => $this->font(
            $font,
            $headlineSize,
            '#000014',
        )->align($alignment));

        $host = 'DJ '.($live['host'] ?? 'Equipe Rede Akiba');
        $image->text($host, $isStory ? intdiv($width, 2) : $portraitX, $portraitY + $portraitSize + 46, fn (FontFactory $font) => $this->font(
            $font,
            $isStory ? 34 : 28,
            '#fffaf3',
        )->align($isStory ? 'center' : 'left'));
    }

    private function drawLiveFeedArtwork(
        ImageInterface $image,
        array $live,
        int $width,
        int $height,
    ): void {
        $image->fill('#000014');
        $image->drawPolygon(fn (PolygonFactory $polygon) => $polygon
            ->point(0, 0)
            ->point(720, 0)
            ->point(460, 1350)
            ->point(0, 1350)
            ->background('#000036'));
        $image->drawPolygon(fn (PolygonFactory $polygon) => $polygon
            ->point(720, 0)
            ->point($width, 0)
            ->point($width, $height)
            ->point(460, $height)
            ->background('#002080'));
        $image->drawPolygon(fn (PolygonFactory $polygon) => $polygon
            ->point(0, 925)
            ->point(540, 760)
            ->point($width, 980)
            ->point($width, 1110)
            ->point(540, 890)
            ->point(0, 1060)
            ->background('#0059c0'));
        $image->place($this->halftonePattern($width, $height, '#0091ff'));

        $logo = $this->manager
            ->read($this->requiredAsset($this->brandLogoPath, 'brand logo'))
            ->scaleDown(width: 320, height: 120);
        $image->drawRectangle(390, 100, fn (RectangleFactory $rectangle) => $rectangle
            ->size(300, 88)
            ->background('#ffaa35'));
        $image->text('NO AR', intdiv($width, 2), 144, fn (FontFactory $font) => $this->font(
            $font,
            44,
            '#000036',
        )->align('center')->valign('middle'));

        $portraitBorder = $this->circularImage(
            $this->manager->create(520, 520)->fill('#000036'),
            520,
        );
        $portrait = $this->readImage($live['host_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
            ->cover(496, 496, 'center');
        $portrait = $this->circularImage($portrait, 496);
        $image->place($portraitBorder, 'top-left', 280, 240);
        $image->place($portrait, 'top-left', 292, 252);

        $programImagePath = $this->localPath($live['program_image'] ?? null);
        $placeholderPath = public_path('img/placeholders/program.webp');
        $hostY = 1005;

        if ($programImagePath !== null
            && is_file($programImagePath)
            && realpath($programImagePath) !== realpath($placeholderPath)) {
            $programLogo = $this->manager
                ->read($programImagePath)
                ->scaleDown(width: 650, height: 190);
            $programLogoX = intdiv($width - $programLogo->width(), 2);
            $programLogoY = 800 + intdiv(190 - $programLogo->height(), 2);
            $image->place($programLogo, 'top-left', $programLogoX, $programLogoY);
        } else {
            $program = Str::upper($live['program'] ?? 'Programa ao vivo');
            $programLines = $this->wrap($program, 66, 850, 2);
            $hostY = 900 + ((count($programLines) - 1) * 88);

            $image->text(implode("\n", $programLines), intdiv($width, 2), 815, fn (FontFactory $font) => $this->font(
                $font,
                66,
                '#fffaf3',
                1.34,
            )->align('center'));
        }

        $image->text('DJ '.Str::upper($live['host'] ?? 'Equipe Rede Akiba'), intdiv($width, 2), $hostY, fn (FontFactory $font) => $this->font(
            $font,
            38,
            '#fffaf3',
        )->align('center'));

        $image->place($logo, 'top-left', intdiv($width - $logo->width(), 2), 1165);

    }

    private function drawFacebookLiveArtwork(
        ImageInterface $image,
        array $live,
        int $width,
        int $height,
    ): void {
        $image->fill('#000014');
        $image->drawPolygon(fn (PolygonFactory $polygon) => $polygon
            ->point(0, 0)
            ->point(700, 0)
            ->point(520, $height)
            ->point(0, $height)
            ->background('#000036'));
        $image->drawPolygon(fn (PolygonFactory $polygon) => $polygon
            ->point(700, 0)
            ->point($width, 0)
            ->point($width, $height)
            ->point(520, $height)
            ->background('#002080'));
        $image->place($this->halftonePattern($width, $height, '#0091ff'));

        $portraitBorder = $this->circularImage(
            $this->manager->create(370, 370)->fill('#ffaa35'),
            370,
        );
        $portrait = $this->readImage($live['host_avatar'] ?? null, public_path('img/placeholders/avatar.webp'))
            ->cover(346, 346, 'center');
        $portrait = $this->circularImage($portrait, 346);
        $image->place($portraitBorder, 'top-left', 80, 130);
        $image->place($portrait, 'top-left', 92, 142);

        $image->drawRectangle(525, 105, fn (RectangleFactory $rectangle) => $rectangle
            ->size(260, 72)
            ->background('#ffaa35'));
        $image->text('NO AR', 655, 141, fn (FontFactory $font) => $this->font(
            $font,
            38,
            '#000036',
        )->align('center')->valign('middle'));

        $program = Str::upper($live['program'] ?? 'Programa ao vivo');
        $programLines = $this->wrap($program, 58, 590, 3);
        $image->text(implode("\n", $programLines), 525, 225, fn (FontFactory $font) => $this->font(
            $font,
            58,
            '#fffaf3',
            1.18,
        ));
        $image->text('DJ '.Str::upper($live['host'] ?? 'Equipe Rede Akiba'), 525, 455, fn (FontFactory $font) => $this->font(
            $font,
            28,
            '#ffaa35',
        ));

        $logo = $this->manager
            ->read($this->requiredAsset($this->brandLogoPath, 'brand logo'))
            ->scaleDown(width: 230, height: 80);
        $image->place($logo, 'top-left', $width - 50 - $logo->width(), $height - 42 - $logo->height());
    }

    private function font(FontFactory $font, int $size, string $color, float $lineHeight = 1): FontFactory
    {
        return $font
            ->filename($this->requiredAsset($this->fontPath, 'font'))
            ->size($size)
            ->color($color)
            ->valign('top')
            ->lineHeight($lineHeight);
    }

    private function readImage(?string $source, string $fallback): ImageInterface
    {
        $path = $this->localPath($source);

        if ($path === null || ! is_file($path)) {
            $path = $fallback;
        }

        if (! is_file($path)) {
            throw new RuntimeException("Social artwork image not found [{$path}].");
        }

        return $this->manager->read($path);
    }

    private function localPath(?string $source): ?string
    {
        if (blank($source)) {
            return null;
        }

        if (is_file($source)) {
            return $source;
        }

        if (Str::startsWith($source, ['http://', 'https://'])) {
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);

            if (parse_url($source, PHP_URL_HOST) !== $appHost) {
                return null;
            }

            $source = parse_url($source, PHP_URL_PATH) ?: '';
        }

        if (str_starts_with($source, '/storage/')) {
            return storage_path('app/public/'.substr($source, strlen('/storage/')));
        }

        if (str_starts_with($source, '/')) {
            return public_path(ltrim($source, '/'));
        }

        return public_path(ltrim($source, '/'));
    }

    private function wrap(string $text, int $size, int $maxWidth, int $maxLines): array
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if ($this->textWidth($word, $size) > $maxWidth) {
                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }

                while ($this->textWidth($word, $size) > $maxWidth && count($lines) < $maxLines) {
                    $part = $this->fittingPrefix($word, $size, $maxWidth);
                    $lines[] = $part;
                    $word = mb_substr($word, mb_strlen($part));
                }

                if (count($lines) === $maxLines) {
                    break;
                }

                $line = $word;

                continue;
            }

            $candidate = $line === '' ? $word : "{$line} {$word}";

            if ($line !== '' && $this->textWidth($candidate, $size) > $maxWidth) {
                $lines[] = $line;
                $line = $word;

                if (count($lines) === $maxLines) {
                    break;
                }
            } else {
                $line = $candidate;
            }
        }

        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        if (count($lines) === $maxLines && implode(' ', $lines) !== implode(' ', $words)) {
            $lastLine = rtrim($lines[$maxLines - 1], '.,;:');
            $lines[$maxLines - 1] = $this->fittingPrefix(
                $lastLine,
                $size,
                $maxWidth - $this->textWidth('…', $size),
            ).'…';
        }

        return $lines;
    }

    private function textWidth(string $text, int $size): int
    {
        $box = imagettfbbox($size, 0, $this->requiredAsset($this->fontPath, 'font'), $text);

        return max($box[0], $box[2], $box[4], $box[6]) - min($box[0], $box[2], $box[4], $box[6]);
    }

    private function fittingPrefix(string $text, int $size, int $maxWidth): string
    {
        $low = 1;
        $high = mb_strlen($text);

        while ($low < $high) {
            $middle = (int) ceil(($low + $high) / 2);

            if ($this->textWidth(mb_substr($text, 0, $middle), $size) <= $maxWidth) {
                $low = $middle;
            } else {
                $high = $middle - 1;
            }
        }

        return mb_substr($text, 0, max(1, $low));
    }

    private function inspect(string $contents): array
    {
        $size = getimagesizefromstring($contents);

        if ($size === false || ! isset($size['mime'])) {
            throw new RuntimeException('Generated social artwork is not a valid image.');
        }

        if (! in_array($size['mime'], ['image/jpeg', 'image/webp'], true)) {
            throw new RuntimeException("Generated social artwork has invalid MIME type [{$size['mime']}].");
        }

        if (strlen($contents) > self::MAX_FILE_SIZE) {
            throw new RuntimeException('Generated social artwork exceeds the 8 MB limit.');
        }

        return [(int) $size[0], (int) $size[1], $size['mime']];
    }

    private function pathSegment(string $value): string
    {
        $segment = preg_replace('/[^a-zA-Z0-9_-]/', '-', $value) ?? '';
        $segment = trim($segment, '-');

        if ($segment === '') {
            throw new InvalidArgumentException('Social artwork version identifiers cannot be empty.');
        }

        return $segment;
    }

    private function requiredAsset(?string $path, string $name): string
    {
        if ($path === null || ! is_file($path)) {
            throw new RuntimeException("Social artwork {$name} is unavailable.");
        }

        return $path;
    }
}
