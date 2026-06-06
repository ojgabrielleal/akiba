<?php

namespace App\Actions\Post;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Services\Process\ImageProcessService;
use InvalidArgumentException;

use App\Models\Post;
use App\Models\User;

class UpdatePostAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(Post $post, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null, string $module = 'post'): Post
    {
        return DB::transaction(function () use ($post, $data, $image, $cover, $module) {
             switch ($module) {
                case 'post':
                    $post = $this->updatePost($post, $data, $image, $cover);
                    break;
                case 'review':
                    $post = $this->updateReview($post, $data, $image, $cover);
                break;
                case 'event':
                    $post = $this->updateEvent($post, $data, $image, $cover);
                break;
                default:
                    throw new InvalidArgumentException("Invalid post update type [{$module}].");
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

            return $post;
        });
    }

    public function updatePost(Post $post, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post->fill($this->postData(
            $post,
            $data,
            $image,
            $cover,
            $data['status'],
            $data['content']
        ));

        if ($post->isDirty()){
            $post->save();
        }

        return $post;
    }

    public function updateReview(Post $post, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post->fill($this->postData($post, $data, $image, $cover));

        if ($post->isDirty()){
            $post->save();
        }

        $user = User::where('uuid', $data['review']['author']['uuid'])->first();
        $review = $post->review()->first();

        $review->update([
            'year_of_release' => $data['year_of_release'],
            'sinopse' => $data['sinopse'],
        ]);

        $review->opinions()->updateOrCreate(
            ['uuid' => $data['review']['uuid']],
            [
                'user_id' => $user->id,
                'status' => $data['review']['status'],
                'content' => $data['review']['content'],
            ]
        );

        return $post;
    }

    public function updateEvent(Post $post, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post->fill($this->postData(
            $post,
            $data,
            $image,
            $cover,
            $data['status'],
            $data['content']
        ));

        if ($post->isDirty()){
            $post->save();
        }

        $event = $post->event()->first();
        $event->update([
            'dates' => $data['dates'],
            'address' => $data['address'],
        ]);

        return $post;
    }

    public function postData(Post $post, array $data, ?UploadedFile $image, ?UploadedFile $cover, string $status = 'published', ?string $content = null): array
    {
        return [
            'status' => $status,
            'title' =>  $data['title'],
            'content' => $content,
            'image' => $this->image->store('posts', $image, 'public', $post->image),
            'cover' => $this->image->store('posts', $cover, 'public', $post->cover),
        ];
    }
}
