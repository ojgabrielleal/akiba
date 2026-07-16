<?php

namespace App\Actions\Podcast;

use App\Services\Process\ImageProcessService;

use App\Models\User;
use App\Models\Podcast;
use Illuminate\Support\Facades\DB;

class StorePodcastAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data,): Podcast
    {
        return DB::transaction(fn () => Podcast::create([
            'user_id' => $user->id,
            'image' => $this->image->store('podcasts', $data['image']),
            'season' => $data['season'],
            'episode' => $data['episode'],
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description' => $data['description'],
            'audio' => $data['audio'],
        ]));
    }
}
