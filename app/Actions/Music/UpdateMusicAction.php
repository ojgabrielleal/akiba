<?php

namespace App\Actions\Music;

use App\Models\Music;
use App\Services\Process\ImageProcessService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateMusicAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Music $music, array $data, ?UploadedFile $image = null, ?UploadedFile $imageRanking = null): Music
    {
        return DB::transaction(function () use ($music, $data, $image, $imageRanking) {
            $music->fill([
                'type' => $data['type'],
                'production' => $data['production'],
                'artist' => $data['artist'],
                'name' => $data['name'],
                'image' => $this->image->store('musics', $image, $music->image),
                'image_ranking' => $this->image->store('musics/ranking', $imageRanking, $music->image_ranking),
            ]);

            if ($music->isDirty()) {
                $music->save();
            }

            return $music;
        });
    }
}
