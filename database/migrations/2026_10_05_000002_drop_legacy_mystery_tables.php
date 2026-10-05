<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mystery_interactions');
        Schema::dropIfExists('mysteries');
    }

    public function down(): void
    {
        // Legacy mystery tables were replaced by enigmagames and enigmagame_interactions.
    }
};
