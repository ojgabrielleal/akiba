<?php

namespace Database\Factories;

use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Podcast>
 */
class PodcastFactory extends Factory
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
            'title' => fake()->word(),
            'image' => $this->fakeImageUrl(),
            'season' => fake()->numberBetween(1, 10),
            'episode' => fake()->numberBetween(1, 100),
            'summary' => fake()->paragraph(),
            'description' => fake()->paragraph(),
            'audio' => fake()->url(),
        ];
    }
}
