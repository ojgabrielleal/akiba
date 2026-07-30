<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('public_label')->nullable()->after('label');
        });

        collect([
            'administrator' => 'Administradores',
            'developer' => 'Desenvolvedores',
            'locutioner' => 'Locutores',
            'marketing' => 'Marketing',
            'podcaster' => 'Podcasters',
            'social_media' => 'Social Media',
            'writer' => 'Colunistas',
        ])->each(function (string $publicLabel, string $name) {
            DB::table('roles')
                ->where('name', $name)
                ->update(['public_label' => $publicLabel]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('public_label');
        });
    }
};
