<?php

namespace App\Http\Controllers\Private;

use App\Services\RepositoryService;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\RepositoryResource;

use App\Models\Repository;

use Inertia\Inertia;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Requests\Repository\StoreRepositoryRequest;
use App\Http\Requests\Repository\UpdateRepositoryRequest;

class RepositoryController extends Controller
{
    use HasFlashMessages;

    use ResolvesAuthorizedProps;

    private $render = 'private/Marketing';

    public function __construct(
        private RepositoryService $repositoryFilter,
    ) {}

    private function indexRepositories()
    {
        return $this->whenCanViewAny(Repository::class,
            fn () => RepositoryResource::collection(
                $this->repositoryFilter->filter(['active' => true])
            )->format('grouped'),
        );
    }

    public function showRepository(Repository $repository)
    {
        $this->authorize('view', $repository);

        return Inertia::render($this->render, [
            'repository' => $this->indexRepository($repository),
        ]);
    }

    public function storeRepository(StoreRepositoryRequest $request, RepositoryService $service)
    {
        $service->store($request->validated(), $request->file('image'));

        return $this->flashMessage('save');
    }

    public function updateRepository(UpdateRepositoryRequest $request, RepositoryService $service, Repository $repository)
    {
        $service->update($repository, $request->validated(), $request->file('image'));

        return $this->flashMessage('update');
    }

    public function deactivateRepository(RepositoryService $service, Repository $repository)
    {
        $this->authorize('deactivate', $repository);

        $service->deactivate($repository);

        return $this->flashMessage('deactivate');
    }

    private function indexRepository(Repository $repository): RepositoryResource
    {
        return new RepositoryResource($repository);
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'repositories' => $this->indexRepositories(),
        ]);
    }
}
