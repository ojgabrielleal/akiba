<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\ProgramAirtime;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->first();
        $user = User::where('id', '!=', 1)->where('is_virtual', false)->inRandomOrder()->first();
        $virtualUser = User::where('is_virtual', true)->inRandomOrder()->first();

        $this->seedAdministrator($admin);
        $this->seedPrograms($user);
        $this->seedAutoDJ($user);
        $this->seedPlaylist($virtualUser);
        $this->seedScheduled($virtualUser);
    }

    private function seedAdministrator(?User $user): void
    {
        if (! $user) {
            return;
        }

        $program = Program::factory()
            ->withPrivate()
            ->for($user, 'host')
            ->create();

        $this->seedProgramAirtimes($program);
    }

    private function seedPrograms(?User $user): void
    {
        if (! $user) {
            return;
        }

        $free = Program::factory()
            ->withFree()
            ->for($user, 'host')
            ->create();

        $private = Program::factory()
            ->withPrivate()
            ->for($user, 'host')
            ->create();

        $this->seedProgramAirtimes($free);
        $this->seedProgramAirtimes($private);
    }

    private function seedAutoDJ(?User $user): void
    {
        if (! $user) {
            return;
        }

        Program::factory()
            ->withAutoDJ()
            ->asDefault()
            ->for($user, 'host')
            ->create();
    }

    private function seedPlaylist(?User $user): void
    {
        if (! $user) {
            return;
        }

        Program::factory()
            ->withPlaylist()
            ->for($user, 'host')
            ->create();
    }

    private function seedScheduled(?User $user): void
    {
        if (! $user) {
            return;
        }

        Program::factory()
            ->withScheduled()
            ->for($user, 'host')
            ->create();
    }

    private function seedProgramAirtimes(Program $program): void
    {
        if ($program->execution_mode !== 'live') {
            return;
        }

        ProgramAirtime::factory(3)
            ->for($program, 'program')
            ->create();
    }
}
