<?php

namespace Tests\Unit\Services\Radio;

use App\Services\MusicService;
use App\Models\Music;
use App\Processing\ImageProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MusicServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesMusicData(): void
    {
        $music = Music::factory()->create([
            'type' => 'OP',
            'production' => 'Old Anime',
            'artist' => 'Old Artist',
            'name' => 'Old Song',
            'image' => '/storage/images/musics/old.webp',
        ]);

        $service = new MusicService(new ImageProcess());

        $service->update($music, [
            'type' => 'ED',
            'production' => 'New Anime',
            'artist' => 'New Artist',
            'name' => 'New Song',
        ]);

        $music->refresh();

        $this->assertSame('ED', $music->type);
        $this->assertSame('New Anime', $music->production);
        $this->assertSame('New Artist', $music->artist);
        $this->assertSame('New Song', $music->name);
        $this->assertSame('/storage/images/musics/old.webp', $music->image);
    }
}
