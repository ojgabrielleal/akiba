<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropMusicForeignKeyIfExists();

        Schema::table('song_requests', function (Blueprint $table) {
            $table->foreignId('music_id')->nullable()->change();
            $table->foreign('music_id')->references('id')->on('music')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('song_requests')->whereNull('music_id')->delete();

        $this->dropMusicForeignKeyIfExists();

        Schema::table('song_requests', function (Blueprint $table) {
            $table->foreignId('music_id')->nullable(false)->change();
            $table->foreign('music_id')->references('id')->on('music')->cascadeOnDelete();
        });
    }

    private function dropMusicForeignKeyIfExists(): void
    {
        $foreignKey = DB::selectOne(<<<'SQL'
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'song_requests'
                AND COLUMN_NAME = 'music_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        SQL);

        if (! $foreignKey?->name) {
            return;
        }

        Schema::table('song_requests', function (Blueprint $table) use ($foreignKey) {
            $table->dropForeign($foreignKey->name);
        });
    }
};
