<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PostSocialImageController extends Controller
{
    public function __invoke(Post $post)
    {
        abort_unless($post->is_active && $post->status === 'published', 404);

        $image = $post->cover ?: $post->image;
        abort_unless($image && str_starts_with($image, '/storage/'), 404);

        $path = str_replace('/storage/', '', $image);
        abort_unless(Storage::disk('public')->exists($path), 404);

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
