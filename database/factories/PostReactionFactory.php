<?php

namespace Database\Factories;

use App\Models\OAuthAccount;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostReaction>
 */
class PostReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reactor_type' => OAuthAccount::class,
            'reactor_id' => OAuthAccount::factory(),
            'name' => fake()->randomElement(['angry', 'duvid', 'content', 'happy', 'big-happy']),
        ];
    }
}
