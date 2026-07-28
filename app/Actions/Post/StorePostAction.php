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
            $post = $this->storePost($user, $data, $image, $cover);
            $this->storeTags($post, $data['tags'] ?? []);
            $this->storeReferences($post, $data['references'] ?? []);
            $this->storeReview($post, $user, $data['review'] ?? []);

            return $post;
        });
    }

    private function storePost(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        return Post::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'status' => $data['status'] ?? 'published',
            'content' => $data['content'] ?? null,
            'image' => $this->image->store('posts', $image),
            'cover' => $this->image->store('posts', $cover),
            'module' => $data['module'] ?? 'post',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    private function storeTags(Post $post, array $tags): void
    {
        if (! empty($tags)) {
            $post->tags()->createMany($tags);
        }
    }

    private function storeReferences(Post $post, array $references): void
    {
        if (! empty($references)) {
            $post->references()->createMany($references);
        }
    }

    private function storeReview(Post $post, User $user, array $review): void
    {
        if (empty($review)) {
            return;
        }

        $post->reviews()->create([
            'user_id' => $user->id,
            'status' => $review['status'],
            'content' => $review['content'],
        ]);
    }
}
