<?php

namespace App\Http\Controllers\Private;

use App\Actions\ListenerMonth\StoreListenerMonthAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\ListenerMonth\StoreListenerMonthRequest;

class ListenerMonthController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreListenerMonthAction $storeListenerMonthAction,
    ) {}

    public function store(StoreListenerMonthRequest $request)
    {
        $this->storeListenerMonthAction->execute();

        return $this->flashMessage('save');
    }
}
