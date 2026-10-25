<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostSocialPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostSocialPublication>
 */
class PostSocialPublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'caption' => fake()->sentence(),
            'hashtags' => ['Akiba', 'Anime'],
            'artwork_path' => fake()->url().'/social/post.jpg',
            'requested_at' => now(),
        ];
    }
}
