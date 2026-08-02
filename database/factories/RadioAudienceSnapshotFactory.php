<?php

namespace Database\Factories;

use App\Models\RadioStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RadioAudienceSnapshot>
 */
class RadioAudienceSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'radio_station_id' => RadioStation::factory(),
            'listeners' => fake()->numberBetween(0, 200),
            'status' => 'online',
            'response_time_ms' => fake()->numberBetween(50, 2000),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'listeners' => null,
            'status' => 'offline',
            'response_time_ms' => null,
        ]);
    }

    public function invalidResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'listeners' => null,
            'status' => 'invalid_response',
        ]);
    }
}
