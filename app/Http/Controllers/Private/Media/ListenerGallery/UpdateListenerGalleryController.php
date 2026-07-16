<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Actions\ListenerGallery\UpdateListenerGalleryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListenerGallery\UpdateListenerGalleryRequest;
use App\Models\ListenerGallery;

class UpdateListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateListenerGalleryRequest $request, ListenerGallery $listenerGallery, UpdateListenerGalleryAction $updateListenerGalleryAction)
    {
        $updateListenerGalleryAction->execute($listenerGallery, $request->validated(), $request->file('image'));

        return $this->flashMessage('update');
    }
}
