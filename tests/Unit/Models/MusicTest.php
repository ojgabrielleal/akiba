<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Anime;
use App\Models\Music;

class MusicTest extends TestCase
{
    use RefreshDatabase;

    public function testAnimeRelationship(): void
    {
        $anime = Anime::factory()->create();
        $music = Music::factory()->for($anime, 'anime')->create();

        $this->assertTrue($music->anime->is($anime));
    }

    /**
     * Tests from Music model scopes.
     */
    public function testScopeRanking(): void
    {
        $rankedMusic = Music::factory()->create([
            'in_ranking' => true
        ]);

        $notRankedMusic = Music::factory()->create([
            'in_ranking' => false
        ]);

        $manualRankedMusic = Music::factory()->create([
            'in_ranking' => true,
            'is_manual' => true,
        ]);

        $musics = Music::inRanking()->get();

        $this->assertTrue($musics->contains($rankedMusic));
        $this->assertFalse($musics->contains($notRankedMusic));
        $this->assertFalse($musics->contains($manualRankedMusic));
    }

    /**
     * Tests from Music model static query methods.
     */
    public function testMostRequested(): void
    {
        $leastRequestedMusic = Music::factory()->create([
            'song_requests_total' => 5,
        ]);

        $secondMostRequestedMusic = Music::factory()->create([
            'song_requests_total' => 20,
        ]);

        $mostRequestedMusic = Music::factory()->create([
            'song_requests_total' => 30,
        ]);

        $manualMostRequestedMusic = Music::factory()->create([
            'song_requests_total' => 40,
            'is_manual' => true,
        ]);

        $thirdMostRequestedMusic = Music::factory()->create([
            'song_requests_total' => 10,
        ]);

        $musics = Music::mostRequested();

        $this->assertCount(3, $musics);
        $this->assertTrue($musics[0]->is($mostRequestedMusic));
        $this->assertTrue($musics[1]->is($secondMostRequestedMusic));
        $this->assertTrue($musics[2]->is($thirdMostRequestedMusic));
        $this->assertFalse($musics->contains($leastRequestedMusic));
        $this->assertFalse($musics->contains($manualMostRequestedMusic));
    }
}
