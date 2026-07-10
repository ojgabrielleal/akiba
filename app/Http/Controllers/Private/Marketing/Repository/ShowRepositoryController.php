<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Http\Controllers\Controller;
use App\Http\Resources\RepositoryResource;
use App\Models\Repository;

class ShowRepositoryController extends Controller
{
    public function __invoke(Repository $repository)
    {
        $this->authorize('view', $repository);

        return new RepositoryResource($repository);
    }
}
