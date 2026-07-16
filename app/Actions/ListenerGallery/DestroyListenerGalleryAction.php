<?php

namespace App\Actions\ListenerGallery;

use App\Models\ListenerGallery;
use App\Services\Process\ImageProcessService;
use Illuminate\Support\Facades\DB;

class DestroyListenerGalleryAction
{
    public function __construct(private ImageProcessService $image)
    {
    }

    public function execute(ListenerGallery $listenerGallery): void
    {
        DB::transaction(function () use ($listenerGallery) {
            $this->image->delete($listenerGallery->image);
            $listenerGallery->delete();
        });
    }
}
