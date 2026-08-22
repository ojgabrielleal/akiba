<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->string('status')->default('visible')->after('comment')->index();
            $table->foreignId('moderated_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
            $table->text('moderation_reason')->nullable()->after('moderated_at');
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropColumn([
                'status',
                'moderated_at',
                'moderation_reason',
            ]);
        });
    }
};
