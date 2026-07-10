<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameTableIfNeeded('socials', 'user_socials');
        $this->renameTableIfNeeded('preferences', 'user_preferences');
        $this->renameTableIfNeeded('favorities', 'user_favorites');

        $this->renameTableIfNeeded('reactions', 'post_reactions');
        $this->renameTableIfNeeded('references', 'post_references');
        $this->renameTableIfNeeded('tags', 'post_tags');

        $this->renameTableIfNeeded('options', 'poll_options');

        $this->renameTableIfNeeded('songs_requests', 'song_requests');
        $this->renameTableIfNeeded('playlist_battle', 'playlist_battles');
        $this->renameTableIfNeeded('onair', 'onairs');
        $this->renameTableIfNeeded('calendar', 'calendars');
        $this->renameTableIfNeeded('listener_month', 'listener_months');
        $this->renameTableIfNeeded('musics', 'music');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameTableIfNeeded('music', 'musics');
        $this->renameTableIfNeeded('listener_months', 'listener_month');
        $this->renameTableIfNeeded('calendars', 'calendar');
        $this->renameTableIfNeeded('onairs', 'onair');
        $this->renameTableIfNeeded('playlist_battles', 'playlist_battle');
        $this->renameTableIfNeeded('song_requests', 'songs_requests');

        $this->renameTableIfNeeded('poll_options', 'options');

        $this->renameTableIfNeeded('post_tags', 'tags');
        $this->renameTableIfNeeded('post_references', 'references');
        $this->renameTableIfNeeded('post_reactions', 'reactions');

        $this->renameTableIfNeeded('user_favorites', 'favorities');
        $this->renameTableIfNeeded('user_preferences', 'preferences');
        $this->renameTableIfNeeded('user_socials', 'socials');
    }

    private function renameTableIfNeeded(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }
};
