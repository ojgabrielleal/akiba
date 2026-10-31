<?php

namespace Tests\Unit\Services;

use App\Models\Onair;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Program;
use App\Models\User;
use App\Services\SocialArtworkDataService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SocialArtworkDataServiceTest extends TestCase
{
    public function test_it_maps_post_and_live_locution_to_the_visual_contract(): void
    {
        Carbon::setTestNow('2026-10-24 18:00:00');

        $author = new User([
            'nickname' => 'Akemi',
            'avatar' => '/storage/users/akemi.webp',
        ]);
        $host = new User([
            'nickname' => 'Yagami',
            'avatar' => '/storage/users/yagami.webp',
        ]);
        $program = new Program([
            'name' => 'Conexão Akiba',
            'image' => '/storage/programs/conexao.webp',
        ]);
        $program->setRelation('host', $host);

        $post = new Post([
            'title' => str_repeat('Uma notícia muito importante para os fãs de anime ', 4),
            'module' => 'review',
            'cover' => '/storage/posts/capa.webp',
        ]);
        $post->slug = 'review-akiba';
        $post->created_at = Carbon::parse('2026-10-24 12:30:00', 'America/Sao_Paulo');
        $post->setRelation('author', $author);
        $post->setRelation('tags', collect([new PostTag(['name' => 'Anime'])]));

        $onair = new Onair(['in_air' => true]);
        $onair->setRelation('host', $host);
        $onair->setRelation('program', $program);

        $artwork = app(SocialArtworkDataService::class)->map($post, $onair);

        $this->assertSame('Anime', $artwork['category']);
        $this->assertSame('/storage/posts/capa.webp', $artwork['cover']);
        $this->assertSame('Akemi', $artwork['author']);
        $this->assertSame('/storage/users/akemi.webp', $artwork['author_avatar']);
        $this->assertSame('Conexão Akiba', $artwork['live']['program']);
        $this->assertSame('Yagami', $artwork['live']['host']);
        $this->assertSame('/storage/users/yagami.webp', $artwork['live']['host_avatar']);
        $this->assertSame('24 OUT 2026', $artwork['published_at']);
        $this->assertStringEndsWith('/review/review-akiba', $artwork['url']);
        $this->assertSame($artwork['title'], $artwork['titles']['instagram-feed']);
        $this->assertLessThanOrEqual(80, mb_strlen($artwork['titles']['instagram-story']));
        $this->assertSame($artwork['title'], $artwork['titles']['facebook-feed']);
        $this->assertLessThanOrEqual(110, mb_strlen($artwork['titles']['discord-post']));
        $this->assertStringEndsWith('…', $artwork['titles']['instagram-story']);
    }

    public function test_it_uses_safe_fallbacks_when_optional_data_is_missing(): void
    {
        $post = new Post([
            'title' => '',
            'module' => 'unknown',
        ]);
        $post->slug = 'conteudo';
        $post->setRelation('author', null);

        $artwork = app(SocialArtworkDataService::class)->map($post);

        $this->assertSame('Conteúdo novo na Rede Akiba', $artwork['title']);
        $this->assertSame('Notícia', $artwork['category']);
        $this->assertSame('Rede Akiba', $artwork['author']);
        $this->assertSame('Programa ao vivo', $artwork['live']['program']);
        $this->assertSame('Equipe Rede Akiba', $artwork['live']['host']);
        $this->assertStringContainsString('/img/placeholders/placeholder.webp', $artwork['cover']);
    }

    public function test_it_maps_post_review_and_event_to_their_public_categories_and_urls(): void
    {
        foreach ([
            'post' => ['Notícia', '/materia/conteudo-post'],
            'review' => ['Review', '/review/conteudo-review'],
            'event' => ['Evento', '/event/conteudo-event'],
        ] as $module => [$category, $urlSuffix]) {
            $post = new Post([
                'title' => "Conteúdo {$module}",
                'module' => $module,
            ]);
            $post->slug = "conteudo-{$module}";
            $post->setRelation('author', null);

            $artwork = app(SocialArtworkDataService::class)->map($post);

            $this->assertSame($category, $artwork['category']);
            $this->assertStringEndsWith($urlSuffix, $artwork['url']);
        }
    }

    public function test_it_maps_live_program_data_for_social_artwork(): void
    {
        $host = new User([
            'nickname' => 'Yagami',
            'avatar' => '/storage/users/yagami.webp',
        ]);
        $program = new Program([
            'name' => 'Conexão Akiba',
            'image' => '/storage/programs/conexao.webp',
        ]);
        $program->setRelation('host', $host);

        $onair = new Onair(['in_air' => true]);
        $onair->setRelation('host', $host);
        $onair->setRelation('program', $program);

        $artwork = app(SocialArtworkDataService::class)->mapLive($onair);

        $this->assertSame('Conexão Akiba', $artwork['live']['program']);
        $this->assertSame('/storage/programs/conexao.webp', $artwork['live']['program_image']);
        $this->assertSame('Yagami', $artwork['live']['host']);
        $this->assertSame('/storage/users/yagami.webp', $artwork['live']['host_avatar']);
        $this->assertSame('Ao vivo', $artwork['live']['status']);
        $this->assertSame('Sintonize agora', $artwork['live']['call_to_action']);
    }
}
