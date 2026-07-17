<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StorePostAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        return DB::transaction(function () use ($user, $data, $image, $cover) {
            $post = Post::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'status' => $data['status'] ?? "published",
                'content' => $data['content'] ?? null,
                'image' => $this->image->store('posts', $image),
                'cover' => $this->image->store('posts', $cover),
                'module' => $data['module'] ?? 'post',
                'metadata' => $data['metadata'] ?? null,
            ]);

            if (!empty($data['tags'])) {
                $post->tags()->createMany($data['tags']);
            }

            if (!empty($data['references'])) {
                $post->references()->createMany($data['references']);
            }

            if (!empty($data['review'])) {
                $post->reviews()->create([
                    'user_id' => $user->id,
                    'status' => $data['review']['status'],
                    'content' => $data['review']['content'],
                ]);
            }

            return $post;
        });
    }
}
