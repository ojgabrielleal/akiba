<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BadgeAssignment;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BadgeAssignmentController extends Controller
{
    public function markAsSeen(Request $request, BadgeAssignment $assignment): RedirectResponse
    {
        $member = AuthenticatedMember::fromRequest($request);

        abort_unless($member, 401);

        abort_unless(
            $assignment->owner_type === $member->getMorphClass()
            && (string) $assignment->owner_id === (string) $member->getKey(),
            403,
        );

        if (! $assignment->seen_at) {
            $assignment->update(['seen_at' => now()]);
        }

        return back();
    }
}
