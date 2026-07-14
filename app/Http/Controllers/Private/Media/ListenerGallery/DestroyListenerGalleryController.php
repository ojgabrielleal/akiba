<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\ListenerGallery;
use App\Services\Process\ImageProcessService;

class DestroyListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(ListenerGallery $listenerGallery, ImageProcessService $image)
    {
        $this->authorize('delete', $listenerGallery);

        $image->delete($listenerGallery->image);
        $listenerGallery->delete();

        return $this->flashMessage('delete');
    }
}
