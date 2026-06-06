<?php

namespace App\Actions\Post;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Services\Process\ImageProcessService;

use InvalidArgumentException;

use App\Models\User;
use App\Models\Post;

class CreatePostAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $cover = null, string $module = 'post'): Post
    {
        return DB::transaction(function () use ($user, $data, $image, $cover, $module) {
            switch ($module) {
                case 'post':
                    $post = $this->createPost($user, $data, $image, $cover);
                    break;
                case 'review':
                    $post = $this->createReview($user, $data, $image, $cover);
                    break;
                case 'event':
                    $post = $this->createEvent($user, $data, $image, $cover);
                    break;
                default:
                    throw new InvalidArgumentException("Invalid post create type [{$module}].");
            }

            if (!empty($data['tags'])) {
                $post->tags()->createMany($data['tags']);
            }

            if (!empty($data['references'])) {
                $post->references()->createMany($data['references']);
            }

            return $post;
        });
    }

    public function createPost(User $user, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post = Post::create($this->postData(
            $user,
            $data,
            $image,
            $cover,
            $data['status'],
            $data['content']
        ));

        return $post;
    }

    public function createReview(User $user, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post = Post::create($this->postData(
            $user,
            $data,
            $image,
            $cover
        ));

        $review = $post->review()->create([
            'year_of_release' => $data['year_of_release'],
            'sinopse' => $data['sinopse'],
        ]);

        $review->opinions()->create([
            'user_id' => $user->id,
            'status' => $data['review']['status'],
            'content' => $data['review']['content'],
        ]);

        return $post;
    }

    public function createEvent(User $user, array $data, ?UploadedFile $image, ?UploadedFile $cover): Post
    {
        $post = Post::create($this->postData(
            $user,
            $data,
            $image,
            $cover,
            $data['status'],
            $data['content']
        ));

        $post->event()->create([
            'dates' => $data['dates'],
            'address' => $data['address'],
        ]);

        return $post;
    }

    public function postData(User $user, array $data, ?UploadedFile $image, ?UploadedFile $cover, string $status = 'published', ?string $content = null): array
    {
        return [
            'user_id' => $user->id,
            'title' => $data['title'],
            'status' => $status,
            'content' =>  $content,
            'image' => $this->image->store('posts', $image, 'public'),
            'cover' => $this->image->store('posts', $cover, 'public'),
        ];
    }
}
