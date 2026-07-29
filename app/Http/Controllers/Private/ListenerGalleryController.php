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

use Inertia\Inertia;

class ListenerGalleryController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Media';

    public function show(ListenerGallery $listenerGallery)
    {
        $this->authorize('view', $listenerGallery);

        return Inertia::render($this->render, [
            'listenerGallery' => $this->indexListenerGallery($listenerGallery),
        ]);
    }

    private function indexListenerGallery(ListenerGallery $listenerGallery): ListenerGalleryResource
    {
        return new ListenerGalleryResource($listenerGallery);
    }

    public function store(StoreListenerGalleryRequest $request, StoreListenerGalleryAction $action)
    {
        $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('save');
    }

    public function update(UpdateListenerGalleryRequest $request, UpdateListenerGalleryAction $action, ListenerGallery $listenerGallery)
    {
        $action->execute(
            $listenerGallery,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }

    public function destroy(DestroyListenerGalleryAction $action, ListenerGallery $listenerGallery)
    {
        $this->authorize('delete', $listenerGallery);

        $action->execute($listenerGallery);

        return $this->flashMessage('delete');
    }
}
