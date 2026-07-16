<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Actions\Repository\DeactivateRepositoryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Repository;

class DeactivateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Repository $repository, DeactivateRepositoryAction $deactivateRepositoryAction)
    {
        $this->authorize('delete', $repository);

        $deactivateRepositoryAction->execute($repository);

        return $this->flashMessage('deactivate');
    }
}
