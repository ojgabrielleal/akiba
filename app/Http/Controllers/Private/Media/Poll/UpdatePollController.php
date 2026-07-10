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
            'question' => $request->input('question'),
        ]);

        $options = $poll->options->values();

        $mapped = [
            'option_one' => $options->get(0),
            'option_two' => $options->get(1),
            'option_three' => $options->get(2),
            'option_four' => $options->get(3),
        ];

        foreach ($mapped as $key => $option) {
            if ($option) {
                $option->update([
                    'option' => $request->input($key),
                ]);
            }
        }

        return $this->flashMessage('update');
    }
}
