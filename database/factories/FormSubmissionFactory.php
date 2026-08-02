<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_type' => fake()->randomElement(['contact', 'partnership', 'generic']),
            'name' => fake()->name(),
            'contact' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'payload' => [
                'message' => fake()->paragraph(),
            ],
        ];
    }

    public function approved(): static
    {
        return $this->reviewed('approved');
    }

    public function rejected(): static
    {
        return $this->reviewed('rejected');
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    private function reviewed(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
