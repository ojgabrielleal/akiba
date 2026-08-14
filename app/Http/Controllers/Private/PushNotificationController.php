<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushNotificationController extends Controller
{
    public function storePushNotification(Request $request, PushNotificationService $service): Response
    {
        if ($request->boolean('silent')) {
            $service->store($request->user(), $request->all());
        } else {
            $service->storeWithActivationNotification($request->user(), $request->all(), '/panel');
        }

        return response()->noContent();
    }

    public function destroyPushNotification(Request $request, PushNotificationService $service): Response
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $service->destroy($request->user(), $data['endpoint']);

        return response()->noContent();
    }
}
