<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Repository\DeactivateRepositoryAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Repository;

class DeactivateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private DeactivateRepositoryAction $deactivateRepositoryAction,
    ) {}

    public function __invoke(Repository $repository)
    {
        $this->authorize('deactivate', $repository);

        $this->deactivateRepositoryAction->execute($repository);

        return $this->flashMessage('deactivate');
    }
}
