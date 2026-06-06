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
        Schema::rename('polls_options', 'options');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('options', 'polls_options');
    }
};
