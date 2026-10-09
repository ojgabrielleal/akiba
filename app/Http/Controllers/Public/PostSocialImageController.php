<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PostSocialImageController extends Controller
{
    public function __invoke(Post $post)
    {
        if (! $post->is_active || $post->status !== 'published') {
            Log::warning('Post social image denied for unpublished/inactive post', [
                'post_uuid' => $post->uuid,
                'status' => $post->status,
                'is_active' => $post->is_active,
            ]);

            abort(404);
        }

        $image = $post->cover ?: $post->image;

        if (! $image) {
            Log::warning('Post social image missing source image', ['post_uuid' => $post->uuid]);
            abort(404);
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return redirect()->away($image);
        }

        if (! str_starts_with($image, '/storage/')) {
            Log::warning('Post social image has unsupported source path', [
                'post_uuid' => $post->uuid,
                'image' => $image,
            ]);

            abort(404);
        }

        $path = str_replace('/storage/', '', $image);

        if (! Storage::disk('public')->exists($path)) {
            Log::warning('Post social image file not found', [
                'post_uuid' => $post->uuid,
                'path' => $path,
                'image' => $image,
            ]);

            abort(404);
        }

        $manager = new ImageManager(new Driver());
        $jpg = $manager
            ->read(Storage::disk('public')->get($path))
            ->toJpeg(88);

        return response((string) $jpg, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
