<?php

namespace App\Http\Controllers\Private\Administration\Activity;

use App\Actions\Administration\Activity\CreateActivityAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\CreateActivityRequest;

class StoreActivityController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateActivityRequest $request, CreateActivityAction $createActivityAction)
    {
        $createActivityAction->execute($request->user(), $request->validated());

        return $this->flashMessage('save');
    }
}
