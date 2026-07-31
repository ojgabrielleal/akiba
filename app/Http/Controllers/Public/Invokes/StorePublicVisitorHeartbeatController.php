<?php

namespace App\Http\Controllers\Public\Invokes;

use App\Actions\OnlineVisitors\StorePublicVisitorHeartbeatAction;
use App\Http\Controllers\Controller;
use App\Models\OAuthAccount;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorePublicVisitorHeartbeatController extends Controller
{
    public function __invoke(Request $request, StorePublicVisitorHeartbeatAction $action): JsonResponse
    {
        $data = $request->validate([
            'visitor_id' => ['required', 'string', 'max:100'],
            'path' => ['nullable', 'string', 'max:255'],
            'is_listening' => ['nullable', 'boolean'],
        ]);

        $oauthAccount = $request->attributes->get('oauth_account');

        $visitor = $action->execute(
            $data['visitor_id'],
            $data['path'] ?? null,
            (bool) ($data['is_listening'] ?? false),
            $oauthAccount instanceof OAuthAccount ? $oauthAccount : null,
        );

        return response()->json([
            'visitor' => $visitor,
        ]);
    }
}
