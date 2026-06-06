<?php

namespace Database\Factories;

use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Music>
 */
class MusicFactory extends Factory
{
    use HasFakeImages;

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
            'image' => $this->fakeImageUrl(),
            'artist' => fake()->name(),
            'name' => fake()->name(),
            'in_ranking' => fake()->boolean(),
            'image_ranking' => $this->fakeImageUrl(),
            'song_requests_total' => fake()->randomDigit(),
        ];
    }
}
