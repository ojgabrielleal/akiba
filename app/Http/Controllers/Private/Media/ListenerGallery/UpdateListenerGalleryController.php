<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Media\UpdateListenerGalleryRequest;
use App\Models\ListenerGallery;
use App\Services\Process\ImageProcessService;

class UpdateListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateListenerGalleryRequest $request, ListenerGallery $listenerGallery, ImageProcessService $image) {
        
    $listenerGallery->fill([
            'image' => $image->store('listener-gallery', $request->file('image'), $listenerGallery->image),
            'caption' => $request->input('caption'),
            'listener_name' => $request->input('listener_name'),
        ]);

        if ($listenerGallery->isDirty()) {
            $listenerGallery->save();
        }

        return $this->flashMessage('update');
    }
}
