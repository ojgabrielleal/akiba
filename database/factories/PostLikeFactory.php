<?php

namespace Database\Factories;

use App\Models\OAuthAccount;
use App\Models\Post;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostLike>
 */
class PostLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'liker_type' => null,
            'liker_id' => null,
            'visitor_token' => hash('sha256', Str::uuid()->toString()),
        ];
    }

    public function withOAuthAccount(): static
    {
        return $this->state(fn () => [
            'liker_type' => OAuthAccount::class,
            'liker_id' => OAuthAccount::factory(),
            'visitor_token' => null,
        ]);
    }
}
