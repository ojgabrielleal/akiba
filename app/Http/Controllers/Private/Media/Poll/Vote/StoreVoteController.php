<?php

namespace App\Http\Controllers\Private\Media\Poll\Vote;

use App\Actions\Poll\StorePollVoteAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\PollOption;
use Illuminate\Http\Request;

class StoreVoteController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, PollOption $option, StorePollVoteAction $storePollVoteAction)
    {
        $this->authorize('vote', $option);

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

        $storePollVoteAction->execute($option, $request->user());

        return $this->flashMessage('save');
    }
}
