<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calendar>
 */
class CalendarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hour' => fake()->time(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'date' => fake()->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek())->format('Y-m-d'),
            'type' => fake()->randomElement(['show', 'live', 'video', 'podcast']),
            'content' => fake()->word(),
        ];
    }

    public function withActivity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => null,
        ]);
    }
}
