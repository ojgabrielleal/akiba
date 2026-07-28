<?php

namespace App\Http\Controllers\Private\Invokes;

use App\Actions\Poll\StorePollVoteAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Models\PollOption;

use Illuminate\Http\Request;

class PollVoteController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Request $request, StorePollVoteAction $action, PollOption $option)
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

        $action->execute($option, $request->user());

        return $this->flashMessage('save');
    }
}
