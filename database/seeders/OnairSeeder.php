<?php

namespace Database\Seeders;

use App\Models\Onair;
use App\Models\Program;
use Illuminate\Database\Seeder;

class OnairSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAutoDj();
        $this->seedLive();
        $this->seedScheduled();
        $this->seedPlaylist();
    }

    private function seedAutoDj(): void
    {
        $program = Program::where('execution_mode', 'auto_dj')
            ->orderByDesc('is_default_auto_dj')
            ->first();

        if (! $program) {
            return;
        }

        Onair::factory()
            ->for($program, 'program')
            ->autoDj()
            ->create(['in_air' => true]);
    }

    private function seedLive(): void
    {
        $program = Program::where('execution_mode', 'live')
            ->whereHas('programAirtimes')
            ->with('programAirtimes')
            ->first();

        if (! $program) {
            return;
        }

        Onair::factory()
            ->for($program, 'program')
            ->live()
            ->create(['in_air' => false]);
    }

    private function seedScheduled(): void
    {
        $program = Program::where('execution_mode', 'scheduled')->first();

        if (! $program) {
            return;
        }

        Onair::factory()
            ->for($program, 'program')
            ->scheduled()
            ->create(['in_air' => false]);
    }

    private function seedPlaylist(): void
    {
        $program = Program::where('execution_mode', 'playlist')->first();

        if (! $program) {
            return;
        }

        Onair::factory()
            ->for($program, 'program')
            ->playlist()
            ->create(['in_air' => false]);
    }
}
