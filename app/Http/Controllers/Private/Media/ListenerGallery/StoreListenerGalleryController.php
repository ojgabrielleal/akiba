<?php

namespace App\Http\Controllers\Private\Media\ListenerGallery;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Media\CreateListenerGalleryRequest;
use App\Models\ListenerGallery;
use App\Services\Process\ImageProcessService;

class StoreListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateListenerGalleryRequest $request, ImageProcessService $image)
    {
        ListenerGallery::create([
            'user_id' => $request->user()->id,
            'image' => $image->store('listener-gallery', $request->file('image')),
            'caption' => $request->input('caption'),
            'listener_name' => $request->input('listener_name'),
        ]);

        return $this->flashMessage('save');
    }
}
