<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Actions\ListenerGallery\DestroyListenerGalleryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\ListenerGallery;

class DestroyListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(ListenerGallery $listenerGallery, DestroyListenerGalleryAction $destroyListenerGalleryAction)
    {
        $this->authorize('delete', $listenerGallery);

        $destroyListenerGalleryAction->execute($listenerGallery);

        return $this->flashMessage('delete');
    }
}
