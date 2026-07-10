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
            'question' => $request->input('question'),
        ]);

        $options = [
            $request->input('option_one'),
            $request->input('option_two'),
            $request->input('option_three'),
            $request->input('option_four'),
        ];

        foreach ($options as $text) {
            $poll->options()->create([
                'option' => $text,
            ]);
        }

        return $this->flashMessage('save');
    }
}
