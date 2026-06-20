<?php

namespace App\Actions\Radio\ListenerMonth;

use Illuminate\Http\UploadedFile;
use App\Services\Process\ImageProcessService;

use App\Models\ListenerMonth;

use InvalidArgumentException;

class CreateListenerMonthAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(array $data, ?UploadedFile $avatar = null)
    {
        $found = ListenerMonth::mostActiveListenerOfCurrentMonth();
        if (!$found) return null;

        return ListenerMonth::first()->update([
            'avatar' => $this->image->store('listener-month', $avatar, 'public'),
            'name' => $found->name,
            'birthday' => $data['birthday'],
            'address' => $found->address,
            'favorite_program' => $found->favorite_program,
            'favorite_music' => $found->favorite_music,
            'requests_total' => $found->requests_total,
        ]);
    }
}
