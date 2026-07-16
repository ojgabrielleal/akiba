<?php

namespace App\Http\Controllers\Private\Radio\ListenerMonth;

use App\Actions\ListenerMonth\StoreListenerMonthAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListenerMonth\StoreListenerMonthRequest;

class StoreListenerMonthController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreListenerMonthRequest $request, StoreListenerMonthAction $storeListenerMonthAction)
    {
        $storeListenerMonthAction->execute(
            $request->validated(),
            $request->file('avatar')
        );

        return $this->flashMessage('save');
    }
}
