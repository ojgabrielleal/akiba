<?php

namespace App\Http\Controllers\Private\Administration\Activity;

use App\Actions\Activity\StoreActivityAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\StoreActivityRequest;

class StoreActivityController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreActivityRequest $request, StoreActivityAction $storeActivityAction)
    {
        $storeActivityAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }
}
