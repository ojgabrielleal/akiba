<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\ProgramSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('is_virtual', false)->first() ?? User::first();

        if (! $user) {
            return;
        }

        Program::active()
            ->whereNotIn('execution_mode', ['playlist', 'auto_dj'])
            ->get()
            ->each(function (Program $program, int $index) use ($user) {
                $startAt = now()
                    ->addDays($index + 1)
                    ->setTime(14, 0);

                ProgramSchedule::factory()
                    ->for($user)
                    ->for($program)
                    ->create([
                        'action' => 'start_program',
                        'scheduled_at' => $startAt,
                        'status' => 'pending',
                    ]);
            });
    }
}
