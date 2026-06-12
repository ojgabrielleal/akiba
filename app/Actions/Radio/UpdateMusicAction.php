<?php

namespace App\Actions\Radio;

use App\Models\Music;
use App\Services\Process\ImageProcessService;
use Illuminate\Http\UploadedFile;

class UpdateMusicAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Music $music, array $data, ?UploadedFile $image = null): Music
    {
        $music->fill([
            'type' => $data['type'],
            'production' => $data['production'],
            'artist' => $data['artist'],
            'name' => $data['name'],
            'image' => $this->image->store('musics', $image, 'public', $music->image),
        ]);

        if ($music->isDirty()) {
            $music->save();
        }

        return $music;
    }
}
