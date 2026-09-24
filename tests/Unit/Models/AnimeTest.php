<?php

namespace Tests\Unit\Models;

use App\Models\Anime;
use App\Models\Music;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimeTest extends TestCase
{
    use RefreshDatabase;

    public function testMusicsRelationship(): void
    {
        $anime = Anime::factory()->create();
        $music = Music::factory()->for($anime, 'anime')->create();

        $this->assertTrue($anime->musics->contains($music));
    }

    public function testScopeCompleteDiscography(): void
    {
        $complete = Anime::factory()->completeDiscography()->create();
        $partial = Anime::factory()->create([
            'discography_status' => Anime::DISCOGRAPHY_PARTIAL,
        ]);

        $animes = Anime::withCompleteDiscography()->get();

        $this->assertTrue($animes->contains($complete));
        $this->assertFalse($animes->contains($partial));
    }
}
