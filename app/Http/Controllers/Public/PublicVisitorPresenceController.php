<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Process\PublicVisitorPresenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PublicVisitorPresenceController extends Controller
{
    public function heartbeat(Request $request, PublicVisitorPresenceService $presence): JsonResponse
    {
        $data = $request->validate([
            'visitor_token' => ['required', 'string', 'max:80'],
            'url' => ['nullable', 'string', 'max:500'],
            'path' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'listening' => ['nullable', 'boolean'],
            'player_loading' => ['nullable', 'boolean'],
            'identity' => ['nullable', 'array'],
        ]);

        try {
            $presence->heartbeat($request, $data);
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json(null, 204);
    }
}
