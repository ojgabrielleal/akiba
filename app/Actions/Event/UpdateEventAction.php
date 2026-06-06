<?php

namespace App\Actions\Event;

use App\Services\Process\ImageProcessService;
use App\Models\Event;

class UpdateEventAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Event $event, array $data): Event
    {
        $event->fill([
            'image' => $this->image->store('events', $data['image'], 'public', $event->image),
            'cover' => $this->image->store('events', $data['cover'], 'public', $event->cover),
            'title' => $data['title'],
            'content' => $data['content'],
            'dates' => $data['dates'],
            'address' => $data['address'],
        ]);

        if ($event->isDirty()){
            $event->save();
        }

        return $event;
    }
}
