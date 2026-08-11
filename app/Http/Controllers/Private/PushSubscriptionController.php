<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    public function storePushSubscription(Request $request, PushNotificationService $service): Response
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'content_encoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ]);

        $service->store($request->user(), $data);

        return response()->noContent();
    }

    public function destroyPushSubscription(Request $request, PushNotificationService $service): Response
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $service->destroy($request->user(), $data['endpoint']);

        return response()->noContent();
    }
}
