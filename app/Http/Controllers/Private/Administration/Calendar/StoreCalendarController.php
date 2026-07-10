<?php

namespace App\Http\Controllers\Private\Administration\Calendar;

use App\Actions\Administration\Calendar\CreateCalendarAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Administration\CreateCalendarRequest;
use App\Models\User;

class StoreCalendarController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateCalendarRequest $request, CreateCalendarAction $createCalendarAction)
    {
        $createCalendarAction->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }
}
