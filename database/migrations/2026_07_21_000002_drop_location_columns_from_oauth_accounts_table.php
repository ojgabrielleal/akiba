<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'country']);
        });
    }

    public function down(): void
    {
        Schema::table('oauth_accounts', function (Blueprint $table) {
            $table->string('city')->nullable()->after('birth_date');
            $table->string('state')->nullable()->after('city');
            $table->char('country', 2)->nullable()->after('state');
        });
    }
};
