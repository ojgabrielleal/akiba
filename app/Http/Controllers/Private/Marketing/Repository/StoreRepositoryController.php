<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Actions\Repository\StoreRepositoryAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Repository\StoreRepositoryRequest;

class StoreRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreRepositoryRequest $request, StoreRepositoryAction $storeRepositoryAction)
    {
        $storeRepositoryAction->execute($request->validated(), $request->file('image'));

        return $this->flashMessage('save');
    }
}
