<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE programs MODIFY user_id BIGINT UNSIGNED NULL');

            Schema::table('programs', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        DB::table('programs')
            ->where('execution_mode', 'live')
            ->where('access_type', 'free')
            ->update(['user_id' => null]);
    }

    public function down(): void
    {
        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');

        if ($fallbackUserId) {
            DB::table('programs')
                ->whereNull('user_id')
                ->update(['user_id' => $fallbackUserId]);
        }

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE programs MODIFY user_id BIGINT UNSIGNED NOT NULL');

            Schema::table('programs', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
