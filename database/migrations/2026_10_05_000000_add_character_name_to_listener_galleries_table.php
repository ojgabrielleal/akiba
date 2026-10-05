<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listener_galleries', function (Blueprint $table) {
            $table->string('character_name')->nullable()->after('listener_name');
        });
    }

    public function down(): void
    {
        Schema::table('listener_galleries', function (Blueprint $table) {
            $table->dropColumn('character_name');
        });
    }
};
