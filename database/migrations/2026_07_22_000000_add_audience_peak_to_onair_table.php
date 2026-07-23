<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onairs', function (Blueprint $table) {
            $table->unsignedInteger('peak_listeners')->default(0)->after('song_requests_total');
            $table->timestamp('peak_listeners_at')->nullable()->after('peak_listeners');

            $table->index('peak_listeners');
        });
    }

    public function down(): void
    {
        Schema::table('onairs', function (Blueprint $table) {
            $table->dropIndex(['peak_listeners']);
            $table->dropColumn(['peak_listeners', 'peak_listeners_at']);
        });
    }
};
