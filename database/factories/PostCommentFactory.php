<?php

namespace Database\Factories;

use App\Models\OAuthAccount;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostComment>
 */
class PostCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_type' => OAuthAccount::class,
            'author_id' => OAuthAccount::factory(),
            'comment' => fake()->paragraph(),
        ];
    }
}
