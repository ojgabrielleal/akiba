<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('song_request_blocks', function (Blueprint $table) {
            $table->foreignId('blocked_by_id')
                ->nullable()
                ->after('requester_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('song_request_blocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blocked_by_id');
        });
    }
};
