<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Resources\Onair\OnairStreamResource;

use App\Models\Onair;

use App\Services\External\StreamService;

class StreamController extends Controller
{
    protected $stream;

    public function __construct(StreamService $stream)
    {
        $this->stream = $stream;
    }

    public function showMetadata()
    {
        $stream = $this->stream->data();

        $onair = Onair::live()->with('program.host')->get();

        $onair->each(function ($item) use ($stream) {
            $item->streaming_data = $stream ?? [];
        });

        return OnairStreamResource::collection($onair);
    }
}
