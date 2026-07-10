<?php

namespace App\Http\Controllers\Private\Administration\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Resources\CalendarResource;
use App\Models\Calendar;

class ShowCalendarController extends Controller
{
    public function __invoke(Calendar $calendar)
    {
        $this->authorize('view', $calendar);

        return new CalendarResource($calendar->load(['activity', 'responsible']));
    }
}
