<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Repository;

class DeactivateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Repository $repository)
    {
        $this->authorize('delete', $repository);

        $repository->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }
}
