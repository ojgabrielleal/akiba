<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $this->post();
        $this->radio();
        $this->variable();
        $this->locution();
    }

    public function post(): void
    {
        $this->call([
            PostSeeder::class,
            ReviewSeeder::class,
            EventSeeder::class,
        ]);
    }

    public function radio(): void
    {
        $this->call([
            PodcastSeeder::class,
            MusicSeeder::class,
            PlaylistBattleSeeder::class,
            ListenerMonthSeeder::class,
        ]);
    }

    private function locution(): void
    {
        $this->call([
            ProgramSeeder::class,
            PlanSeeder::class,
            OnairSeeder::class,
            SongRequestSeeder::class,
        ]);
    }

    public function variable(): void
    {
        $this->call([
            PollSeeder::class,
            TaskSeeder::class,
            RepositorySeeder::class,
            ActivitySeeder::class,
            CalendarSeeder::class,
        ]);
    }

}
