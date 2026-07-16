<?php

namespace App\Http\Controllers\Private\Administration\Task;

use App\Actions\Task\StoreTaskAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;

class StoreTaskController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreTaskRequest $request, StoreTaskAction $storeTaskAction)
    {
        $storeTaskAction->execute($request->validated());

        return $this->flashMessage('save');
    }
}
