<?php

namespace App\Actions\Podcast;

use App\Services\Process\ImageProcessService;
use App\Models\Podcast;

class UpdatePodcastAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Podcast $podcast, array $data): Podcast
    {
        $podcast->fill([
            'image' => $this->image->store('podcasts', $data['image'], 'public', $podcast->image),
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
    }
}
