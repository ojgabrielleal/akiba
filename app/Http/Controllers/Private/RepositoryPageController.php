<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Models\Repository;

use App\Http\Resources\RepositoryResource;

class RepositoryPageController extends Controller
{
    private $render = 'private/Marketing';

    public function render()
    {
        return Inertia::render($this->render, [
            'repositories' => $this->indexRepositories(),
        ]);
    }

    public function indexRepositories()
    {
        $this->authorize('viewAny', Repository::class);

        return RepositoryResource::collection(
            Repository::active()->get()
        )->format('grouped');
    }

}
