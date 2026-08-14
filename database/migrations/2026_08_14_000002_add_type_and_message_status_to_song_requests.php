<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('song_requests', function (Blueprint $table) {
            $table->string('type', 20)->default('music')->after('uuid');
            $table->boolean('was_read')->default(false)->after('was_canceled');
            $table->boolean('was_dismissed')->default(false)->after('was_read');
        });

        DB::table('song_requests')
            ->whereNull('music_id')
            ->update(['type' => 'message']);
    }

    public function down(): void
    {
        Schema::table('song_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'was_read', 'was_dismissed']);
        });
    }
};
