<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\External\StreamService;

use App\Models\Onair;

use App\Http\Resources\StreamResource;

class StreamController extends Controller
{
    protected $stream;

    public function __construct(StreamService $stream)
    {
        $this->stream = $stream;
    }

    /*
    * ======================
    * STREAM
    * ======================
    */

    public function redirectStream()
    {
        return $this->stream->stream();
    }

    /*
    * ======================
    * METADATA
    * ======================
    */

    public function showMetadata()
    {
        $stream = $this->stream->data();

        $onair = Onair::live()->with('program.host')->get();

        $onair->each(function ($item) use ($stream) {
            $item->streaming_data = $stream ?? [];
        });

        return StreamResource::collection($onair);
    }
}
