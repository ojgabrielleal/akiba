<?php

namespace App\Http\Controllers\Private\Administration\Calendar;

use App\Actions\Calendar\UpdateCalendarAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\UpdateCalendarRequest;
use App\Models\Calendar;
use App\Models\User;

class UpdateCalendarController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateCalendarRequest $request, Calendar $calendar, UpdateCalendarAction $updateCalendarAction)
    {
        $updateCalendarAction->execute(
            $calendar,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('update');
    }
}
