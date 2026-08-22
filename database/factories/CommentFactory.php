<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
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
            'status' => Comment::STATUS_VISIBLE,
        ];
    }
}
