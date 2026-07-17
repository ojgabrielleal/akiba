<?php

namespace App\Http\Controllers\Private;

use App\Actions\ListenerGallery\DestroyListenerGalleryAction;
use App\Actions\ListenerGallery\StoreListenerGalleryAction;
use App\Actions\ListenerGallery\UpdateListenerGalleryAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\ListenerGallery\StoreListenerGalleryRequest;
use App\Http\Requests\ListenerGallery\UpdateListenerGalleryRequest;

use App\Http\Resources\ListenerGalleryResource;

use App\Models\ListenerGallery;

class ListenerGalleryController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DestroyListenerGalleryAction $destroyListenerGalleryAction,
        private StoreListenerGalleryAction $storeListenerGalleryAction,
        private UpdateListenerGalleryAction $updateListenerGalleryAction,
    ) {}

    public function show(ListenerGallery $listenerGallery)
    {
        $this->authorize('view', $listenerGallery);

        return new ListenerGalleryResource($listenerGallery);
    }

    public function store(StoreListenerGalleryRequest $request)
    {
        $this->storeListenerGalleryAction->execute(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('save');
    }

    public function update(UpdateListenerGalleryRequest $request, ListenerGallery $listenerGallery)
    {
        $this->updateListenerGalleryAction->execute(
            $listenerGallery,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }

    public function destroy(ListenerGallery $listenerGallery)
    {
        $this->authorize('delete', $listenerGallery);

        $this->destroyListenerGalleryAction->execute($listenerGallery);

        return $this->flashMessage('delete');
    }
}
