<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Poll\StorePollVoteAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\PollOption;
use App\Support\AuthenticatedMember;

use Illuminate\Http\Request;

class PollVoteController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, StorePollVoteAction $action, PollOption $option)
    {
        $voter = AuthenticatedMember::fromRequest($request);

        abort_unless($voter, 403);

        if ($request->user()) {
            $this->authorize('vote', $option);
        }

        abort_unless(
            $option->poll()
                ->active()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists(),
            403,
        );

        $action->execute($option, $voter);

        return $this->flashMessage('save');
    }
}
