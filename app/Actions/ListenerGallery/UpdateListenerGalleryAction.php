<?php

namespace App\Actions\ListenerGallery;

use App\Models\ListenerGallery;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateListenerGalleryAction
{
    public function __construct(private ImageProcessService $image)
    {
    }

    public function execute(ListenerGallery $listenerGallery, array $data, ?UploadedFile $image = null): ListenerGallery
    {
        return DB::transaction(function () use ($listenerGallery, $data, $image) {
            $listenerGallery->fill([
                'image' => $this->image->store('listener-gallery', $image, $listenerGallery->image),
                'caption' => $data['caption'] ?? null,
                'listener_name' => $data['listener_name'] ?? null,
            ]);

            if ($listenerGallery->isDirty()) {
                $listenerGallery->save();
            }

            return $listenerGallery;
        });
    }
}
