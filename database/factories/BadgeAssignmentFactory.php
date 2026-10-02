<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BadgeAssignment>
 */
class BadgeAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'badge_id' => Badge::factory(),
            'owner_type' => User::class,
            'owner_id' => User::factory(),
            'awarded_by_type' => null,
            'awarded_by_id' => null,
            'reason' => fake()->sentence(),
            'metadata' => null,
            'acquired_at' => now(),
            'revoked_at' => null,
            'revoked_reason' => null,
        ];
    }

    public function forBadgeAndOwner(Badge $badge, User $owner): static
    {
        return $this->state(fn () => [
            'badge_id' => $badge->id,
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->id,
        ]);
    }
}
