<?php

namespace App\Http\Controllers\Private\Radio\ListenerMonth;

use App\Actions\Radio\ListenerMonth\CreateListenerMonthAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Radio\CreateListenerMonthRequest;

class StoreListenerMonthController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateListenerMonthRequest $request, CreateListenerMonthAction $createListenerMonthAction)
    {
        $createListenerMonthAction->execute(
            $request->validated(),
            $request->file('avatar')
        );

        return $this->flashMessage('save');
    }
}
