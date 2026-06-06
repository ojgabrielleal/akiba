<?php

namespace App\Actions\Radio;

use App\Services\Process\ImageProcessService;
use App\Models\ListenerMonth;

class CreateListenerMonthAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(array $data): ?ListenerMonth
    {
        $found = ListenerMonth::mostActiveListenerOfCurrentMonth();

        if (!$found) return null;

        return ListenerMonth::where('id', 1)->update([
            'avatar' => $this->image->store('listener-month', $data['avatar'], 'public'),
            'name' => $found->name,
            'address' => $found->address,
            'favorite_program' => $found->favorite_program,
            'favorite_anime' => $found->favorite_anime,
            'requests_total' => $found->requests_total,
        ]);
    }
}
