<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plannable_type' => Program::class,
            'plannable_id' => Program::factory()->for(User::factory(), 'host'),
            'action' => 'start_program',
            'scheduled_at' => now()->addHour(),
            'status' => 'pending',
        ];
    }

    public function finishProgram(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'finish_program',
        ]);
    }
}
