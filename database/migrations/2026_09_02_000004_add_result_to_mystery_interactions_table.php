<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mystery_interactions', function (Blueprint $table): void {
            $table->string('result')->nullable()->after('admin_response');
        });
    }

    public function down(): void
    {
        Schema::table('mystery_interactions', function (Blueprint $table): void {
            $table->dropColumn('result');
        });
    }
};
