<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mystery_interactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('mystery_id')->constrained()->cascadeOnDelete();
            $table->morphs('participant');
            $table->string('type')->index();
            $table->text('content');
            $table->text('admin_response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('response_notified_at')->nullable();
            $table->timestamps();

            $table->index(['mystery_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mystery_interactions');
    }
};
