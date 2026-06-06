<?php

namespace Database\Factories;

use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Onair>
 */
class OnairFactory extends Factory
{
    use HasFakeImages;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $executionMode = fake()->randomElement(['auto_dj', 'live', 'scheduled', 'playlist']);

        return [
            'in_air' => true,
            'phrase' => [
                'text' => fake()->sentence(),
                'icon' => $this->fakeImageUrl(),
                'decoration' => null,
                 'texture' => null,
            ],
            'execution_mode' => $executionMode,
            'allows_song_requests' => true,
            'song_requests_total' => fake()->randomNumber(),
        ];
    }

    public function withAutoDj(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'auto_dj',
        ]);
    }

    public function autoDj(): static
    {
        return $this->withAutoDj();
    }

    public function playlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'playlist',
        ]);
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'live',
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_mode' => 'scheduled',
        ]);
    }
}
