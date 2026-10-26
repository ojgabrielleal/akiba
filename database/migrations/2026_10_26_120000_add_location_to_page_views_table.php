<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->char('country_code', 2)->nullable()->after('ip_address');
            $table->string('region_code', 16)->nullable()->after('country_code');
            $table->string('city', 120)->nullable()->after('region_code');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('location_accuracy_radius')->nullable()->after('longitude');
            $table->string('timezone', 64)->nullable()->after('location_accuracy_radius');
            $table->index(
                ['country_code', 'region_code', 'city'],
                'page_views_location_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropIndex('page_views_location_index');
            $table->dropColumn([
                'country_code',
                'region_code',
                'city',
                'latitude',
                'longitude',
                'location_accuracy_radius',
                'timezone',
            ]);
        });
    }
};
