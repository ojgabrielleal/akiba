<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('users_socials', 'socials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('socials', 'users_socials');
    }
};
