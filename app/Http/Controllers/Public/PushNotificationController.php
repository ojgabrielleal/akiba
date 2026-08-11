<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushNotificationController extends Controller
{
    public function storePushNotification(Request $request, PushNotificationService $service): Response
    {
        $service->storeWithActivationNotification($request->user(), $request->all());

        return response()->noContent();
    }
}
