<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('animes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('discography_status')->default('unknown');
            $table->timestamp('discography_checked_at')->nullable();
            $table->timestamps();

            $table->index('discography_status');
            $table->index('discography_checked_at');
        });

        Schema::table('music', function (Blueprint $table) {
            $table->foreignId('anime_id')->nullable()->after('uuid')->constrained('animes')->nullOnDelete();
            $table->boolean('is_manual')->default(false)->after('name');
        });

        if (Schema::hasTable('user_top_animes')) {
            Schema::table('user_top_animes', function (Blueprint $table) {
                $table->foreignId('anime_id')->nullable()->after('user_id')->constrained('animes')->nullOnDelete();
            });
        }

        DB::table('music')
            ->select('production', 'image')
            ->whereNotNull('production')
            ->orderBy('production')
            ->get()
            ->groupBy('production')
            ->each(function ($items, string $production) {
                $slug = $this->uniqueAnimeSlug($production);
                $animeId = DB::table('animes')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'name' => $production,
                    'slug' => $slug,
                    'image' => $items->first()->image,
                    'discography_status' => 'unknown',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('music')
                    ->where('production', $production)
                    ->update(['anime_id' => $animeId]);
            });

        if (Schema::hasTable('user_top_animes')) {
            DB::table('user_top_animes')
                ->select('id', 'name', 'slug', 'image')
                ->whereNotNull('name')
                ->orderBy('id')
                ->get()
                ->each(function ($topAnime) {
                    $slug = $topAnime->slug ?: $this->uniqueAnimeSlug($topAnime->name);
                    $anime = DB::table('animes')->where('slug', $slug)->first();

                    $animeId = $anime?->id ?? DB::table('animes')->insertGetId([
                        'uuid' => (string) Str::uuid(),
                        'name' => $topAnime->name,
                        'slug' => $slug,
                        'image' => $topAnime->image,
                        'discography_status' => 'unknown',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('user_top_animes')
                        ->where('id', $topAnime->id)
                        ->update(['anime_id' => $animeId]);
                });
        }

        Schema::table('music', function (Blueprint $table) {
            $table->dropColumn(['production', 'image']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE music MODIFY type ENUM('OP', 'ED', 'OVA') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_top_animes') && Schema::hasColumn('user_top_animes', 'anime_id')) {
            Schema::table('user_top_animes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('anime_id');
            });
        }

        Schema::table('music', function (Blueprint $table) {
            $table->string('production')->nullable()->after('type');
            $table->string('image')->nullable()->after('production');
        });

        DB::table('music')
            ->join('animes', 'music.anime_id', '=', 'animes.id')
            ->update([
                'music.production' => DB::raw('animes.name'),
                'music.image' => DB::raw('animes.image'),
            ]);

        Schema::table('music', function (Blueprint $table) {
            $table->dropColumn('is_manual');
            $table->dropConstrainedForeignId('anime_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE music MODIFY type ENUM('OP', 'ED') NOT NULL");
        }

        Schema::dropIfExists('animes');
    }

    private function uniqueAnimeSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::uuid()->toString();
        $slug = $base;
        $suffix = 2;

        while (DB::table('animes')->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
};
