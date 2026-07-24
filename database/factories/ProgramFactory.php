<?php

namespace Database\Factories;

use Database\Factories\Concerns\HasLocutionIcons;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    use HasLocutionIcons;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'name' => fake()->name(),
            'image' => '/img/placeholders/program.webp',
            'access_type' => 'free',
            'execution_mode' => 'live',
            'is_default_auto_dj' => false,
            'phrases' => null,
        ];
    }

    public function withPlaylist(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'private',
            'execution_mode' => 'playlist',
            'phrases' => null,
        ]);
    }

    public function withAutoDJ(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'private',
            'execution_mode' => 'auto_dj',
            'is_default_auto_dj' => false,
            'phrases' => $this->phrases(),
        ]);
    }

    public function asDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default_auto_dj' => true,
        ]);
    }

    public function withScheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'private',
            'execution_mode' => 'scheduled',
            'phrases' => null,
        ]);
    }

    public function withLive(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'private',
            'execution_mode' => 'live',
            'phrases' => null,
        ]);
    }

    public function withFree(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'free',
            'execution_mode' => 'live',
            'phrases' => null,
        ]);
    }

    public function withPrivate(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_type' => 'private',
            'execution_mode' => 'live',
            'phrases' => null,
        ]);
    }

    private function phrases(): array
    {
        return [
            [
                'text' => fake()->sentence(),
                'icon' => $this->fakeLocutionIcon(),
                'decoration' => 'default',
                'texture' => null,
            ],
            [
                'text' => fake()->sentence(),
                'icon' => $this->fakeLocutionIcon(),
                'decoration' => 'default',
                'texture' => null,
            ],
            [
                'text' => fake()->sentence(),
                'icon' => $this->fakeLocutionIcon(),
                'decoration' => 'default',
                'texture' => null,
            ],
        ];
    }
}
