<?php

namespace App\Http\Controllers\Private\Media\Poll;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Media\UpdatePollRequest;
use App\Models\Poll;

class UpdatePollController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdatePollRequest $request, Poll $poll)
    {
        $poll->update([
            'status' => $request->input('status'),
            'question' => $request->input('question'),
            'expires_at' => $request->input('expires_at'),
        ]);

        $options = $poll->options->keyBy('uuid');

        foreach ($request->input('options') as $option) {
            $options->get($option['uuid'])->update([
                'option' => $option['option'],
            ]);
        }

        return $this->flashMessage('update');
    }
}
