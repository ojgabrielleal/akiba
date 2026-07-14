<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Marketing\UpdateRepositoryRequest;
use App\Models\Repository;
use App\Services\Process\ImageProcessService;

class UpdateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateRepositoryRequest $request, Repository $repository, ImageProcessService $image)
    {
        $repository->fill([
            'name' => $request->input('name', $repository->name),
            'url' => $request->input('url', $repository->url),
            'image' => $image->store('repository', $request->file('image'), $repository->image),
            'type' => $request->input('type', $repository->type),
        ]);

        if ($repository->isDirty()) {
            $repository->save();
        }

        return $this->flashMessage('update');
    }
}
