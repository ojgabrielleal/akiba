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
        Schema::rename('oauth', 'oauth_accounts');

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->renameColumn('provider', 'provider_metadata');

            $table->string('provider')->nullable()->after('uuid');
            $table->string('provider_user_id')->nullable()->after('provider');
            $table->string('username')->nullable()->after('provider_user_id');
            $table->string('nickname')->nullable()->after('username');
            $table->string('avatar')->nullable()->after('nickname');
            $table->date('birth_date')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('birth_date');
            $table->string('state')->nullable()->after('city');
            $table->char('country', 2)->nullable()->after('state');
            $table->text('bio')->nullable()->after('country');
            $table->timestamp('profile_completed_at')->nullable()->after('bio');

            $table->unique(['provider', 'provider_user_id']);
        });

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->dropColumn('provider_metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_user_id']);

            $table->dropColumn([
                'provider',
                'provider_user_id',
                'username',
                'nickname',
                'avatar',
                'birth_date',
                'city',
                'state',
                'country',
                'bio',
                'profile_completed_at',
            ]);
        });

        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->json('provider')->nullable()->after('uuid');
        });

        Schema::rename('oauth_accounts', 'oauth');
    }
};
