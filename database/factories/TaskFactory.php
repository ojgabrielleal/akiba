<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'status' => 'pending',
            'dead_line' => fake()->date(),
            'title' => fake()->words(5, true),
            'description' => fake()->sentence(),
        ];
    }

    public function withDeadline(): static
    {
        return $this->state(fn (array $attributes) => [
            'dead_line' => fake()->dateTimeBetween(
                now()->startOfWeek(), now()->endOfWeek()
            )->format('Y-m-d')
        ]);
    }
}
