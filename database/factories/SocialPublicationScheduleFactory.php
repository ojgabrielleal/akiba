<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\SocialPublicationSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SocialPublicationSchedule>
 */
class SocialPublicationScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'publishable_type' => Post::class,
            'publishable_id' => Post::factory(),
            'channel' => 'instagram',
            'placement' => 'feed',
            'payload' => [
                'caption' => fake()->sentence(),
                'hashtags' => ['Akiba', 'Anime'],
                'post_url' => fake()->url(),
            ],
            'media_path' => 'social-artworks/post/publication/feed.jpg',
            'media_url' => fake()->url().'/storage/social-artworks/post/publication/feed.jpg',
            'status' => SocialPublicationSchedule::STATUS_PENDING,
            'idempotency_key' => 'social:'.Str::uuid(),
            'external_id' => null,
            'last_error' => null,
            'scheduled_at' => now(),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => SocialPublicationSchedule::STATUS_PUBLISHED,
            'external_id' => fake()->numerify('##################'),
            'published_at' => now(),
        ]);
    }
}
