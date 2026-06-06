<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::inRandomOrder()->first();

        if (!$user || Calendar::exists()) return;

        $this->seedHasActivity($user);
        $this->seedNotHasActivity($user);
    }

    public function seedHasActivity(User $user): void
    {
        $activities = Activity::where('allows_confirmations', true)
            ->get();

        if ($activities->isEmpty()) return;

        foreach ($activities as $activity) {
            Calendar::factory()
                ->for($user, 'responsible')
                ->for($activity, 'activity')
                ->withActivity()
                ->create();
        }
    }

    public function seedNotHasActivity(User $user): void
    {
        Calendar::factory(5)
            ->for($user, 'responsible')
            ->create();
    }
}
