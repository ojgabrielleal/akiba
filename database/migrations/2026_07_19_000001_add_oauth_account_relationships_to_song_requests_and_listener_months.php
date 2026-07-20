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
        Schema::table('song_requests', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('address')->nullable()->change();

            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('music_id')
                ->constrained('oauth_accounts')
                ->nullOnDelete();
        });

        Schema::table('listener_months', function (Blueprint $table) {
            $table->dropColumn(['name', 'address', 'avatar', 'birthday']);

            $table->foreignId('oauth_account_id')
                ->nullable()
                ->after('uuid')
                ->constrained('oauth_accounts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listener_months', function (Blueprint $table) {
            $table->dropConstrainedForeignId('oauth_account_id');

            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
            $table->date('birthday')->nullable();
        });

        Schema::table('song_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('oauth_account_id');

            $table->string('name')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
        });
    }
};
