<?php

namespace App\Actions\Event;

use App\Services\Process\ImageProcessService;

use App\Models\User;
use App\Models\Event;

class CreateEventAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data): Event
    {
        return Event::create([
            'user_id' => $user->id,
            'image' => $this->image->store('events', $data['image'], 'public'),
            'cover' => $this->image->store('events', $data['cover'], 'public'),
            'title' => $data['title'],
            'content' => $data['content'],
            'dates' => $data['dates'],
            'address' => $data['address'],
        ]);
    }
}
