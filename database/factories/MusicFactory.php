<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Music>
 */
class MusicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['OP', 'ED']),
            'production' => fake()->word(),
            'image' => '/img/placeholders/avatar.webp',
            'artist' => fake()->name(),
            'name' => fake()->name(),
            'in_ranking' => fake()->boolean(),
            'image_ranking' => '/img/placeholders/avatar.webp',
            'song_requests_total' => fake()->randomDigit(),
        ];
    }
}
