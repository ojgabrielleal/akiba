<?php

namespace App\Actions\Podcast;

use App\Models\Podcast;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdatePodcastAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Podcast $podcast, array $data, ?UploadedFile $image = null): Podcast
    {
        return DB::transaction(function () use ($podcast, $data, $image) {
            $podcast->fill([
                'image' => $this->image->store('podcasts', $image, $podcast->image),
                'season' => $data['season'],
                'episode' => $data['episode'],
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => $data['description'],
                'audio' => $data['audio'],
            ]);

            if ($podcast->isDirty()) {
                $podcast->save();
            }

            return $podcast;
        });
    }
}
