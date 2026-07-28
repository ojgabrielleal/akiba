<?php

namespace App\Actions\Post;

use App\Models\Post;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdatePostAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Post $post, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): Post
    {
        return DB::transaction(function () use ($post, $data, $image, $cover) {
            $this->updatePost($post, $data, $image, $cover);
            $this->syncTags($post, $data['tags'] ?? []);
            $this->syncReferences($post, $data['references'] ?? []);
            $this->updateReview($post, $data['review'] ?? []);

            return $post;
        });
    }

    private function updatePost(Post $post, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null): void
    {
        $post->fill([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'published',
            'content' => $data['content'] ?? null,
            'image' => $this->image->store('posts', $image, $post->image),
            'cover' => $this->image->store('posts', $cover, $post->cover),
            'module' => $data['module'] ?? $post->module,
            'metadata' => $data['metadata'] ?? null,
        ]);

        if ($post->isDirty()) {
            $post->save();
        }
    }

    private function syncTags(Post $post, array $tags): void
    {
        foreach ($tags as $tag) {
            $post->tags()->updateOrCreate(
                ['uuid' => $tag['uuid'] ?? null],
                ['name' => $tag['name']]
            );
        }
    }

    private function syncReferences(Post $post, array $references): void
    {
        foreach ($references as $reference) {
            $post->references()->updateOrCreate(
                ['uuid' => $reference['uuid'] ?? null],
                ['name' => $reference['name'], 'url' => $reference['url']]
            );
        }
    }

    private function updateReview(Post $post, array $review): void
    {
        if (empty($review)) {
            return;
        }

        $post->reviews()->where('uuid', $review['uuid'])->update([
            'status' => $review['status'],
            'content' => $review['content'],
        ]);
    }
}
