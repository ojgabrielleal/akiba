<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\External\CastService;

use App\Models\Onair;

use App\Http\Resources\CastResource;

class CastController extends Controller
{
    protected $cast;

    public function __construct(CastService $cast)
    {
        $this->cast = $cast;
    }

    /*
    * ======================
    * STREAM
    * ======================
    */

    public function redirectStream()
    {
        return $this->cast->stream();
    }

    /*
    * ======================
    * METADATA
    * ======================
    */

    public function showMetadata()
    {
        $cast = $this->cast->data();

        $onair = Onair::live()->with('program.host')->get();

        $onair->each(function ($item) use ($cast) {
            $item->streaming_data = $cast ?? [];
        });

        return CastResource::collection($onair);
    }
}
