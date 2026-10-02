<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BadgeService;
use App\Support\AuthenticatedMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function store(Request $request, BadgeService $service): JsonResponse
    {
        $member = AuthenticatedMember::fromRequest($request);

        if (! $member) {
            abort(401);
        }

        return response()->json([
            'awarded' => $service->awardScheduledPresence($member)->count(),
        ]);
    }
}
