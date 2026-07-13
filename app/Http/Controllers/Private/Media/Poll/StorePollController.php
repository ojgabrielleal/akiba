<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Media\CreatePollRequest;
use App\Models\Poll;

class StorePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreatePollRequest $request)
    {
        $poll = Poll::create([
            'user_id' => $request->user()->id,
            'status' => $request->input('status'),
            'question' => $request->input('question'),
            'expires_at' => $request->input('expires_at'),
        ]);

        $poll->options()->createMany(
            collect($request->input('options'))
                ->map(fn (array $option) => [
                    'option' => $option['option'],
                ])
                ->all()
        );

        return $this->flashMessage('save');
    }
}
