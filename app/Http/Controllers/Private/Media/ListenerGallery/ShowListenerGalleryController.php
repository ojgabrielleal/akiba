<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListenerGalleryResource;
use App\Models\ListenerGallery;

class ShowListenerGalleryController extends Controller
{
    public function __invoke(ListenerGallery $listenerGallery)
    {
        $this->authorize('view', $listenerGallery);
        return new ListenerGalleryResource($listenerGallery);
    }
}
