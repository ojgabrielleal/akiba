<?php

namespace App\Actions\ListenerMonth;

use App\Models\ListenerMonth;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreListenerMonthAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(array $data, ?UploadedFile $avatar = null)
    {
        return DB::transaction(function () use ($data, $avatar) {
            $found = ListenerMonth::mostActiveListenerOfCurrentMonth();

            if (!$found) {
                return null;
            }

            return ListenerMonth::first()->update([
                'avatar' => $this->image->store('listener-month', $avatar),
                'name' => $found->name,
                'birthday' => $data['birthday'],
                'address' => $found->address,
                'favorite_program' => $found->favorite_program,
                'favorite_music' => $found->favorite_music,
                'requests_total' => $found->requests_total,
            ]);
        });
    }
}
