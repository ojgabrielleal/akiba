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

use Inertia\Inertia;

class CalendarController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Administration';

    public function show(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        return Inertia::render($this->render, [
            'calendarItem' => $this->indexCalendar($calendar),
        ]);
    }

    private function indexCalendar(Calendar $calendar): CalendarResource
    {
        return new CalendarResource($calendar->load(['activity', 'responsible']));
    }

    public function store(StoreCalendarRequest $request, StoreCalendarAction $action)
    {
        $action->execute(
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('save');
    }

    public function update(UpdateCalendarRequest $request, UpdateCalendarAction $action, Calendar $calendar)
    {
        $action->execute(
            $calendar,
            User::where('uuid', $request->input('user'))->firstOrFail(),
            $request->validated()
        );

        return $this->flashMessage('update');
    }
}
