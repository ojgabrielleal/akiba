<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radio_audience_snapshots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('radio_station_id')
                ->constrained('radio_stations')
                ->cascadeOnDelete();
            $table->unsignedInteger('listeners')->nullable();
            $table->string('status');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['radio_station_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_audience_snapshots');
    }
};
