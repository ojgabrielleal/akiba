<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BadgeService;
use App\Support\AuthenticatedMember;
use App\Support\EasterEggs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EasterEggController extends Controller
{
    public function catalog(): JsonResponse
    {
        return response()->json(EasterEggs::all());
    }

    public function unlock(Request $request, string $easterEgg, BadgeService $badges): JsonResponse
    {
        abort_unless(EasterEggs::exists($easterEgg), 404);

        $member = AuthenticatedMember::fromRequest($request);

        abort_unless($member, 401);

        return response()->json([
            'awarded' => $badges->awardEasterEggAchievements($easterEgg, $member)->count(),
        ]);
    }
}
