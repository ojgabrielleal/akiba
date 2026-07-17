<?php

namespace App\Http\Controllers\Private;

use App\Actions\Calendar\StoreCalendarAction;
use App\Actions\Calendar\UpdateCalendarAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Calendar\StoreCalendarRequest;
use App\Http\Requests\Calendar\UpdateCalendarRequest;

use App\Http\Resources\Calendar\CalendarResource;

use App\Models\Calendar;
use App\Models\User;

class CalendarController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private StoreCalendarAction $storeCalendarAction,
        private UpdateCalendarAction $updateCalendarAction,
    ) {}

    public function show(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        return new CalendarResource($calendar->load(['activity', 'responsible']));
    }

    public function store(StoreCalendarRequest $request)
    {
        $this->storeCalendarAction->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar)
    {
        $this->updateCalendarAction->execute(
            $calendar,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('update');
    }
}
