<?php

namespace Tests\Unit\Processing;

use App\Processing\SocialArtworkProcess;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class SocialArtworkProcessTest extends TestCase
{
    public function test_it_renders_feed_and_story_with_exact_dimensions(): void
    {
        $process = new SocialArtworkProcess;

        foreach ([
            'instagram-feed' => [1080, 1350],
            'instagram-story' => [1080, 1920],
        ] as $template => $expectedDimensions) {
            $jpeg = $process->render($template, $this->artwork());
            $size = getimagesizefromstring($jpeg);

            $this->assertSame($expectedDimensions, [$size[0], $size[1]]);
            $this->assertSame('image/jpeg', $size['mime']);
            $this->assertLessThan(2_000_000, strlen($jpeg));
        }
    }

    public function test_it_supports_webp_and_rejects_unknown_formats(): void
    {
        $process = new SocialArtworkProcess;
        $webp = $process->render('instagram-feed', $this->artwork(), 'webp', 82);

        $this->assertSame('image/webp', getimagesizefromstring($webp)['mime']);

        $this->expectException(InvalidArgumentException::class);
        $process->render('instagram-feed', $this->artwork(), 'png');
    }

    public function test_it_rejects_an_unknown_template(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SocialArtworkProcess)->render('unknown', $this->artwork());
    }

    public function test_it_renders_every_supported_template_with_exact_dimensions(): void
    {
        $process = new SocialArtworkProcess;

        foreach ([
            'instagram-feed' => [1080, 1350],
            'instagram-live-feed' => [1080, 1350],
            'instagram-story' => [1080, 1920],
            'instagram-live-story' => [1080, 1920],
            'discord-post' => [1200, 630],
        ] as $template => $expectedDimensions) {
            $size = getimagesizefromstring($process->render($template, $this->artwork()));

            $this->assertSame($expectedDimensions, [$size[0], $size[1]], $template);
            $this->assertSame('image/jpeg', $size['mime'], $template);
        }
    }

    public function test_it_stores_immutable_versioned_artworks_and_returns_metadata(): void
    {
        Storage::fake('public');
        $process = new SocialArtworkProcess;

        $first = $process->generate(
            'instagram-feed',
            $this->artwork(),
            'post-uuid',
            'publication-uuid',
        );
        $second = $process->generate(
            'instagram-feed',
            $this->artwork(),
            'post-uuid',
            'publication-uuid',
        );

        Storage::disk('public')->assertExists($first->path);
        Storage::disk('public')->assertExists($second->path);
        $this->assertNotSame($first->path, $second->path);
        $this->assertStringStartsWith(
            'social-artworks/post-uuid/publication-uuid/instagram-feed-',
            $first->path,
        );
        $this->assertSame('image/jpeg', $first->mimeType);
        $this->assertSame([1080, 1350], [$first->width, $first->height]);
        $this->assertGreaterThan(0, $first->size);
        $this->assertStringContainsString($first->path, $first->url);
    }

    public function test_extreme_titles_special_characters_and_emojis_render_deterministically(): void
    {
        $artwork = $this->artwork();
        $artwork['title'] = str_repeat('超長いタイトルÁÉÍÓÚ🚀✨SEM-ESPAÇOS', 30);
        $artwork['titles'] = [];
        $artwork['author'] = 'Akiba & fãs 日本 🎙️';
        $process = new SocialArtworkProcess;

        $first = $process->render('instagram-feed', $artwork);
        $second = $process->render('instagram-feed', $artwork);

        $this->assertSame($first, $second);
        $this->assertSame([1080, 1350], array_slice(getimagesizefromstring($first), 0, 2));
        $this->assertLessThan(2_000_000, strlen($first));
    }

    public function test_missing_cover_and_avatar_use_local_fallbacks(): void
    {
        $artwork = $this->artwork();
        $artwork['cover'] = '/storage/missing-cover.webp';
        $artwork['author_avatar'] = '/storage/missing-avatar.webp';

        $jpeg = (new SocialArtworkProcess)->render('instagram-story', $artwork);

        $this->assertSame([1080, 1920], array_slice(getimagesizefromstring($jpeg), 0, 2));
    }

    public function test_missing_font_fails_with_an_explicit_error(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Social artwork font is unavailable.');

        (new SocialArtworkProcess(fontPath: '/tmp/akiba-missing-font.ttf'))
            ->render('instagram-feed', $this->artwork());
    }

    public function test_it_rejects_invalid_quality_and_empty_version_identifiers(): void
    {
        $process = new SocialArtworkProcess;

        try {
            $process->render('instagram-feed', $this->artwork(), quality: 0);
            $this->fail('Invalid quality should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('between 1 and 100', $exception->getMessage());
        }

        Storage::fake('public');
        $this->expectException(InvalidArgumentException::class);
        $process->generate('instagram-feed', $this->artwork(), '...', 'publication');
    }

    private function artwork(): array
    {
        return [
            'title' => 'Novo projeto de anime ganha janela de estreia e surpreende os fãs',
            'titles' => [
                'instagram-feed' => 'Novo projeto de anime ganha janela de estreia e surpreende os fãs',
                'instagram-story' => 'Novo projeto de anime ganha janela de estreia e surpreende os fãs',
            ],
            'category' => 'Notícia',
            'published_at' => '24 OUT 2026',
            'cover' => public_path('img/placeholders/placeholder.webp'),
            'author' => 'Equipe Rede Akiba',
            'author_avatar' => public_path('img/placeholders/avatar.webp'),
            'live' => [
                'host' => 'Yagami',
                'host_avatar' => public_path('img/placeholders/avatar.webp'),
            ],
        ];
    }
}
