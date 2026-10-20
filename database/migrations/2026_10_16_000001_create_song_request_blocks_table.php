<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_request_blocks', function (Blueprint $table) {
            $table->id();
            $table->morphs('requester');
            $table->unique(['requester_type', 'requester_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_request_blocks');
    }
};
