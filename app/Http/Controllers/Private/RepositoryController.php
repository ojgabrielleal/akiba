<?php

namespace App\Http\Controllers\Private;
use Inertia\Inertia;

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
    private $render = 'private/Administration';

    public function show(Repository $repository)
    {
        $this->authorize('view', $repository);
        return Inertia::render($this->render, [
            'repository' => new RepositoryResource($repository)
        ]);
    }

    public function store(StoreRepositoryRequest $request, StoreRepositoryAction $action)
    {
        $action->execute($request->validated(), $request->file('image'));
        return $this->flashMessage('save');
    }

    public function update(UpdateRepositoryRequest $request, UpdateRepositoryAction $action, Repository $repository)
    {
        $action->execute(
            $repository,
            $request->validated(),
            $request->file('image')
        );

        return $this->flashMessage('update');
    }
}
