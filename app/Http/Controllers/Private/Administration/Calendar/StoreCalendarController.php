<?php

namespace App\Http\Controllers\Private\Administration\Calendar;

use App\Actions\Calendar\StoreCalendarAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\StoreCalendarRequest;
use App\Models\User;

class StoreCalendarController extends Controller
{
    use HasFlashMessages;

    public function __invoke(StoreCalendarRequest $request, StoreCalendarAction $storeCalendarAction)
    {
        $storeCalendarAction->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }
}
