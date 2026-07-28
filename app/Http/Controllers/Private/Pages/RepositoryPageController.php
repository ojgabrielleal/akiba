<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\RepositoryFilter;

use App\Http\Controllers\Concerns\ResolvesAuthorizedProps;
use App\Http\Controllers\Controller;

use App\Http\Resources\RepositoryResource;

use App\Models\Repository;

use Inertia\Inertia;

class RepositoryPageController extends Controller
{
    use ResolvesAuthorizedProps;

    private $render = 'private/Marketing';

    public function __construct(
        private RepositoryFilter $repositoryFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'repositories' => $this->indexRepositories(),
        ]);
    }

    private function indexRepositories()
    {
        return $this->whenCanViewAny(Repository::class,
            fn () => RepositoryResource::collection(
                $this->repositoryFilter->apply(['active' => true])
            )->format('grouped'),
        );
    }
}
