<?php

namespace Database\Factories;

use App\Models\User;
use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
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
            'is_active' => true,
            'user_id' => User::factory(),
            'image' => '/img/placeholders/avatar.webp',
            'title' => fake()->text(),
            'content' => fake()->paragraph(),
            'cover' => $this->fakeImageUrl(640, 360),
            'status' => fake()->randomElement(['published', 'revision', 'draft']),
            'module' => 'post',
            'metadata' => null,
        ];
    }

    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'review',
            'content' => null,
            'status' => 'published',
            'metadata' => [
                'date_of_release' => fake()->date(),
                'sinopse' => fake()->paragraph(),
            ],
        ]);
    }

    public function forModule(string $module): static
    {
        return match ($module) {
            'event' => $this->event(),
            'review' => $this->review(),
            default => $this->state(fn (array $attributes) => [
                'module' => $module,
            ]),
        };
    }

    public function event(): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => 'event',
            'metadata' => [
                'dates' => fake()->date(),
                'event_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
                'address' => fake()->address(),
            ],
        ]);
    }
}
