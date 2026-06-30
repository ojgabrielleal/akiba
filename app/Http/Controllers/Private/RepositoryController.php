<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use App\Services\Process\ImageProcessService;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Repository;

use App\Http\Resources\RepositoryResource;

use App\Http\Requests\Web\Marketing\CreateRepositoryRequest;
use App\Http\Requests\Web\Marketing\UpdateRepositoryRequest;

class RepositoryController extends Controller
{
    use HasFlashMessages;

    private ImageProcessService $image;

    private $render = 'private/Marketing';

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    /*
     * ======================
     * REPOSITORIES
     * ======================
     */

    public function indexRepositories()
    {
        if (request()->user()->cannot('viewAny', Repository::class)) {
            return null;
        }

        return RepositoryResource::collection(
            Repository::active()->get()
        )->format('grouped_by_type');
    }

    public function showRepository(Repository $repository)
    {
        if (request()->user()->cannot('view', $repository)) {
            return null;
        }

        return new RepositoryResource($repository);
    }

    public function createRepository(CreateRepositoryRequest $request)
    {
        Repository::create([
            'name' => $request->input('name'),
            'url' => $request->input('url'),
            'image' => $this->image->store('repository', $request->file('image'), 'public'),
            'type' => $request->input('type'),
        ]);

        return $this->flashMessage('save');
    }

    public function updateRepository(UpdateRepositoryRequest $request, Repository $repository)
    {
        $repository->fill([
            'name' => $request->input('name', $repository->name),
            'url' => $request->input('url', $repository->url),
            'image' => $this->image->store('repository', $request->file('image'), 'public', $repository->image),
            'type' => $request->input('type', $repository->type),
        ]);

        if ($repository->isDirty()) {
            $repository->save();
        }

        return $this->flashMessage('update');
    }

    public function deactivateRepository(Repository $repository)
    {
        if (request()->user()->cannot('delete', $repository)) {
            return null;
        }

        $repository->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'repositories' => $this->indexRepositories(),
        ]);
    }
}
