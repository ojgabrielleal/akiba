<?php

namespace App\Actions\Radio;

use App\Services\Process\ImageProcessService;

use App\Models\Music;

class UpdateMusicRankingAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Music $music, array $data): Music
    {
        $music->update([
            'image_ranking' => $this->image->store('musics/ranking', $data['image'], 'public', $music->image_ranking),
        ]);

        return $music;
    }
}
