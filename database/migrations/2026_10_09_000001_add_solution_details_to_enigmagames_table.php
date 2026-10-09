<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enigmagames', function (Blueprint $table): void {
            $table->string('solution_title')->nullable()->after('solution');
            $table->string('solution_image')->nullable()->after('solution_title');
            $table->text('solution_synopsis')->nullable()->after('solution_image');
        });
    }

    public function down(): void
    {
        Schema::table('enigmagames', function (Blueprint $table): void {
            $table->dropColumn(['solution_title', 'solution_image', 'solution_synopsis']);
        });
    }
};
