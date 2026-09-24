<?php

namespace Database\Factories;

use App\Models\Anime;
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
            'anime_id' => Anime::factory(),
            'type' => fake()->randomElement(['OP', 'ED', 'OVA']),
            'artist' => fake()->name(),
            'name' => fake()->name(),
            'is_manual' => false,
            'in_ranking' => fake()->boolean(),
            'image_ranking' => '/img/placeholders/avatar.webp',
            'song_requests_total' => fake()->randomDigit(),
        ];
    }
}
