<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = $this->listenerMonthTable();

        if (! $tableName || Schema::hasColumn($tableName, 'top_anime')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->json('top_anime')->nullable()->after('favorite_music');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = $this->listenerMonthTable();

        if (! $tableName || ! Schema::hasColumn($tableName, 'top_anime')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('top_anime');
        });
    }

    private function listenerMonthTable(): ?string
    {
        if (Schema::hasTable('listener_months')) {
            return 'listener_months';
        }

        if (Schema::hasTable('listener_month')) {
            return 'listener_month';
        }

        return null;
    }
};
