<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Actions\Repository\UpdateRepositoryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Repository\UpdateRepositoryRequest;
use App\Models\Repository;

class UpdateRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateRepositoryRequest $request, Repository $repository, UpdateRepositoryAction $updateRepositoryAction)
    {
        $updateRepositoryAction->execute($repository, $request->validated(), $request->file('image'));

        return $this->flashMessage('update');
    }
}
