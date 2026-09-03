<?php

namespace Database\Factories;

use App\Models\Mystery;
use App\Models\MysteryInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MysteryInteraction>
 */
class MysteryInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mystery_id' => Mystery::factory(),
            'participant_type' => User::class,
            'participant_id' => User::factory()->withVirtual(),
            'type' => fake()->randomElement([
                MysteryInteraction::TYPE_QUESTION,
                MysteryInteraction::TYPE_FINAL_ANSWER,
            ]),
            'content' => fake()->sentence(8),
            'admin_response' => null,
            'result' => null,
            'responded_by' => null,
            'responded_at' => null,
            'response_notified_at' => null,
        ];
    }

    public function question(): static
    {
        return $this->state(fn () => [
            'type' => MysteryInteraction::TYPE_QUESTION,
            'result' => null,
        ]);
    }

    public function finalAnswer(): static
    {
        return $this->state(fn () => [
            'type' => MysteryInteraction::TYPE_FINAL_ANSWER,
        ]);
    }

    public function answered(?User $responder = null): static
    {
        return $this->state(fn () => [
            'admin_response' => fake()->sentence(10),
            'responded_by' => $responder?->id ?? User::factory(),
            'responded_at' => fake()->dateTimeBetween('-3 days', 'now'),
            'response_notified_at' => now(),
        ]);
    }

    public function correct(?User $responder = null): static
    {
        return $this->finalAnswer()->state(fn () => [
            'result' => 'correct',
            'responded_by' => $responder?->id ?? User::factory(),
            'responded_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    public function incorrect(?User $responder = null): static
    {
        return $this->finalAnswer()->state(fn () => [
            'result' => 'incorrect',
            'responded_by' => $responder?->id ?? User::factory(),
            'responded_at' => fake()->dateTimeBetween('-3 days', 'now'),
        ]);
    }
}
