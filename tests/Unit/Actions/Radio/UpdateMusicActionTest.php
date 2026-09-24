<?php

namespace Tests\Unit\Actions\Radio;

use App\Models\Anime;
use App\Services\MusicService;
use App\Models\Music;
use App\Processing\ImageProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMusicActionTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesMusicData(): void
    {
        $anime = Anime::factory()->create([
            'name' => 'Old Anime',
            'slug' => 'old-anime',
            'image' => '/storage/images/musics/old.webp',
        ]);

        $music = Music::factory()->for($anime, 'anime')->create([
            'type' => 'OP',
            'artist' => 'Old Artist',
            'name' => 'Old Song',
        ]);

        $service = new MusicService(new ImageProcess());

        $service->update($music, [
            'type' => 'ED',
            'anime' => 'New Anime',
            'artist' => 'New Artist',
            'name' => 'New Song',
        ]);

        $music->refresh();

        $this->assertSame('ED', $music->type);
        $this->assertSame('New Anime', $music->anime->name);
        $this->assertSame('New Artist', $music->artist);
        $this->assertSame('New Song', $music->name);
        $this->assertNull($music->anime->image);
    }
}
