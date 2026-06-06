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
        Schema::create('onair', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->boolean('in_air')->default(true);
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('icon')->nullable();
            $table->json('phrase');
            $table->enum('type', ['automatic', 'live', 'scheduled']);
            $table->boolean('allows_song_requests')->default(false);
            $table->integer('song_requests_total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onair');
    }
};
