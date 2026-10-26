<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->string('traffic_source', 120)->default('direct')->after('timezone');
            $table->string('referrer_host')->nullable()->after('traffic_source');
            $table->text('referrer_url')->nullable()->after('referrer_host');
            $table->string('utm_source')->nullable()->after('referrer_url');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->index('traffic_source');
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropIndex(['traffic_source']);
            $table->dropColumn([
                'traffic_source',
                'referrer_host',
                'referrer_url',
                'utm_source',
                'utm_medium',
                'utm_campaign',
            ]);
        });
    }
};
