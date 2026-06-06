<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Models\Event;
use App\Models\Option;
use App\Models\Poll;

use App\Http\Resources\EventResource;
use App\Http\Resources\PollResource;

use App\Http\Requests\Media\CreatePollRequest;
use App\Http\Requests\Media\UpdatePollRequest;

class MediaController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Media';

    /*
     * ======================
     * POLLS
     * ======================
     */

    public function indexPolls()
    {
        if (request()->user()->cannot('viewAny', Poll::class)) {
            return null;
        }

        return PollResource::collection(
            Poll::active()->get()
        );
    }

    public function showPoll(Poll $poll)
    {
        if (request()->user()->cannot('view', $poll)) {
            return null;
        }

        return new PollResource($poll);
    }

    public function createPoll(CreatePollRequest $request)
    {
        if ($request->user()->cannot('create', Poll::class)) {
            return null;
        }

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

    public function updatePoll(UpdatePollRequest $request, Poll $poll)
    {
        if ($request->user()->cannot('update', $poll)) {
            return null;
        }

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

    public function createVote(Option $option)
    {
        if (request()->user()->cannot('update', $option->poll)) {
            return null;
        }

        $option->increment('votes');

        return $this->flashMessage('save');
    }

    public function deactivatePoll(Poll $poll)
    {
        if (request()->user()->cannot('delete', $poll)) {
            return null;
        }

        $poll->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * EVENTS
     * ======================
     */

    public function indexEvents()
    {
        if (request()->user()->cannot('viewAny', Event::class)) {
            return null;
        }

        return EventResource::collection(
            Event::active()->paginate(10)
        );
    }

    public function deactivateEvent(Event $event)
    {
        if (request()->user()->cannot('delete', $event)) {
            return null;
        }

        $event->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'polls' => $this->indexPolls(),
            'events' => $this->indexEvents(),
        ]);
    }
}
