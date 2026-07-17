<?php

namespace App\Http\Controllers\Private;

use App\Actions\Repository\StoreRepositoryAction;
use App\Actions\Repository\UpdateRepositoryAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Repository\StoreRepositoryRequest;
use App\Http\Requests\Repository\UpdateRepositoryRequest;

use App\Http\Resources\RepositoryResource;

use App\Models\Repository;

class RepositoryController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreRepositoryAction $storeRepositoryAction,
        private UpdateRepositoryAction $updateRepositoryAction,
    ) {}

    public function show(Repository $repository)
    {
        $this->authorize('view', $repository);

        return new RepositoryResource($repository);
    }

    public function store(StoreRepositoryRequest $request)
    {
        $this->storeRepositoryAction->execute($request->validated(), $request->file('image'));

        return $this->flashMessage('save');
    }

    public function update(UpdateRepositoryRequest $request, Repository $repository)
    {
        $this->updateRepositoryAction->execute(
            $repository,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }
}
