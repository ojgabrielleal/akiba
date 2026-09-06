<?php

namespace Database\Seeders;

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
            PageViewSeeder::class,
        ]);
    }

    public function radio(): void
    {
        $this->call([
            RadioStationSeeder::class,
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
            ProgramScheduleSeeder::class,
            OnairSeeder::class,
            SongRequestSeeder::class,
        ]);
    }

    public function variable(): void
    {
        $this->call([
            PollSeeder::class,
            PollVoteSeeder::class,
            EnigmaGameSeeder::class,
            ListenerGallerySeeder::class,
            TaskSeeder::class,
            RepositorySeeder::class,
            ActivitySeeder::class,
            CalendarSeeder::class,
        ]);
    }
}
