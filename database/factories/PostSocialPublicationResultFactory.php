<?php

namespace Database\Factories;

use App\Models\PostSocialPublication;
use App\Models\PostSocialPublicationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostSocialPublicationResult>
 */
class PostSocialPublicationResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_social_publication_id' => PostSocialPublication::factory(),
            'channel' => 'instagram',
            'placement' => 'feed',
            'artwork_path' => null,
            'status' => PostSocialPublicationResult::STATUS_PENDING,
            'external_id' => null,
            'attempts' => 0,
            'error' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => PostSocialPublicationResult::STATUS_PUBLISHED,
            'external_id' => fake()->numerify('##################'),
            'attempts' => 1,
            'published_at' => now(),
        ]);
    }
}
