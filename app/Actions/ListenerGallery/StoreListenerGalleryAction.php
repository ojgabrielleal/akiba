<?php

namespace App\Actions\ListenerGallery;

use App\Models\ListenerGallery;
use App\Models\User;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreListenerGalleryAction
{
    public function __construct(
        private ImageProcessService $image
    )
    {}

    public function execute(User $user, array $data, UploadedFile $image): ListenerGallery
    {
        return DB::transaction(fn () => ListenerGallery::create([
            'user_id' => $user->id,
            'image' => $this->image->store('listener-gallery', $image),
            'caption' => $data['caption'] ?? null,
            'listener_name' => $data['listener_name'] ?? null,
        ]));
    }
}
