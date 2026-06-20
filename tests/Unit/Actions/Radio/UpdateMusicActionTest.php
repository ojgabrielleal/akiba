<?php

namespace Tests\Unit\Actions\Radio;

use App\Actions\Radio\Music\UpdateMusicAction;
use App\Models\Music;
use App\Services\Process\ImageProcessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMusicActionTest extends TestCase
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

        $action = new UpdateMusicAction(new ImageProcessService());

        $action->execute($music, [
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
