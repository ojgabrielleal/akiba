<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Actions\ListenerGallery\StoreListenerGalleryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListenerGallery\StoreListenerGalleryRequest;

class StoreListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreListenerGalleryRequest $request, StoreListenerGalleryAction $storeListenerGalleryAction)
    {
        $storeListenerGalleryAction->execute($request->user(), $request->validated(), $request->file('image'));

        return $this->flashMessage('save');
    }
}
