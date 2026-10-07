<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_assignments', function (Blueprint $table): void {
            $table->timestamp('seen_at')->nullable()->after('acquired_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('badge_assignments', function (Blueprint $table): void {
            $table->dropColumn('seen_at');
        });
    }
};
