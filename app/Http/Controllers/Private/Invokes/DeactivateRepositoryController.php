<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Repository\DeactivateRepositoryAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\Repository;

class DeactivateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(DeactivateRepositoryAction $action, Repository $repository)
    {
        $this->authorize('deactivate', $repository);

        $action->execute($repository);

        return $this->flashMessage('deactivate');
    }
}
