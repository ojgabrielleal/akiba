<?php

namespace App\Http\Controllers\Private;

use App\Actions\ListenerMonth\StoreListenerMonthAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\ListenerMonth\StoreListenerMonthRequest;

class ListenerMonthController extends Controller
{
    use HasFlashMessages;

    public function store(StoreListenerMonthRequest $request, StoreListenerMonthAction $action)
    {
        $action->execute();

        return $this->flashMessage('save');
    }
}
