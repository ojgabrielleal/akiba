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
            $post->fill([
                'title' => $data['title'],
                'status' => $data['status'] ?? "published",
                'content' => $data['content'] ?? null,
                'image' => $this->image->store('posts', $image, $post->image),
                'cover' => $this->image->store('posts', $cover, $post->cover),
                'module' => $data['module'] ?? $post->module,
                'metadata' => $data['metadata'] ?? null,
            ]);

            if($post->isDirty()){
                $post->save();
            }
        
            if (!empty($data['tags'])) {
                foreach($data['tags'] as $tag) {
                    $post->tags()->updateOrCreate(
                        ['uuid' => $tag['uuid']],
                        ['name' => $tag['name']]
                    );
                }
            }

            if (!empty($data['references'])) {
                foreach($data['references'] as $reference) {
                    $post->references()->updateOrCreate(
                        ['uuid' => $reference['uuid']],
                        ['name' => $reference['name'], 'url' => $reference['url']]
                    );
                }
            }

            if (!empty($data['review'])) {
                $post->postReviews()->where('uuid', $data['review']['uuid'])->update([
                    'status' => $data['review']['status'],
                    'content' => $data['review']['content']
                ]);
            }

            return $post;
        });
    }
}
