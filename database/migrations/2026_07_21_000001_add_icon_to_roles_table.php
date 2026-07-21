<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('icon')->default('/svg/dots.svg')->after('description');
        });

        $icons = [
            'administrator' => '/svg/crown.svg',
            'developer' => '/svg/cog.svg',
            'locutioner' => '/svg/locution.svg',
            'writer' => '/svg/materials.svg',
            'social_media' => '/svg/media.svg',
            'marketing' => '/svg/marketing.svg',
            'podcaster' => '/svg/podcasts.svg',
        ];

        foreach ($icons as $role => $icon) {
            DB::table('roles')->where('name', $role)->update(['icon' => $icon]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
