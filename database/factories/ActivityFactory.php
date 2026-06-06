<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'content' => fake()->paragraph(),
            'limit' => fake()->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek())->format('Y-m-d'),
        ];
    }

    public function withAllowsConfirmations(): static
    {
        return $this->state(fn (array $attributes) => [
            'allows_confirmations' => true,
        ]);
    }}
