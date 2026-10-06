<?php

namespace App\Console\Commands\Schedules;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PromotePublicSite extends Command
{
    protected $signature = 'site:promote-public {--dry-run : Show what would change without writing files}';

    protected $description = 'Remove provisory public interface and promote /site routes to /.';

    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        $this->removeProvisoryRoutes();
        $this->promotePublicRoutes();
        $this->replaceSiteLinks();
        $this->deleteProvisoryFiles();
        $this->clearCompiledLaravelFiles();
        $this->publishSiteVersion();

        $this->info($this->dryRun
            ? 'Dry run finished. No files were changed.'
            : 'Public site promoted successfully.'
        );

        return self::SUCCESS;
    }

    private function removeProvisoryRoutes(): void
    {
        $path = base_path('routes/web.php');
        $contents = File::get($path);
        $updated = str_replace("require __DIR__.'/web/provisory.php';\n", '', $contents);

        $this->writeWhenChanged($path, $contents, $updated);
    }

    private function promotePublicRoutes(): void
    {
        $path = base_path('routes/web/public.php');
        $contents = File::get($path);
        $updated = str_replace(
            'Route::prefix("site")->middleware([\'oauth.resolve\', \'inertia\', \'auth\'])->group(function () {',
            'Route::prefix("")->middleware([\'oauth.resolve\', \'inertia\'])->group(function () {',
            $contents,
        );
        $updated = str_replace('Route::prefix("site")', 'Route::prefix("")', $updated);
        $updated = str_replace("Route::get('', 'render');", "Route::get('', 'render')->name('home');", $updated);

        $this->writeWhenChanged($path, $contents, $updated);
    }

    private function replaceSiteLinks(): void
    {
        $paths = [
            app_path('Http/Controllers/Public/HomeController.php'),
            app_path('Services/LocutionService.php'),
            resource_path('js/pages/public/NotFound.svelte'),
            resource_path('js/lib/widgets/public/navbar/Navbar.svelte'),
            resource_path('js/lib/widgets/public/footer/Footer.svelte'),
            resource_path('js/lib/widgets/public/form/ProfileForm.svelte'),
            resource_path('js/lib/constants/default/navbar.json'),
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $contents = File::get($path);
            $updated = str_replace([
                '"/site"',
                "'/site'",
                '"/site/',
                "'/site/",
                'url(\'/site\')',
                'redirect(\'/site\')',
            ], [
                '"/"',
                "'/'",
                '"/',
                "'/",
                "url('/')",
                "redirect('/')",
            ], $contents);

            $this->writeWhenChanged($path, $contents, $updated);
        }
    }

    private function deleteProvisoryFiles(): void
    {
        $paths = [
            base_path('routes/web/provisory.php'),
            app_path('Http/Controllers/Provisory'),
            resource_path('js/pages/provisory'),
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                $this->line("Already removed: {$path}");
                continue;
            }

            if ($this->dryRun) {
                $this->line("Would remove: {$path}");
                continue;
            }

            File::isDirectory($path)
                ? File::deleteDirectory($path)
                : File::delete($path);

            $this->line("Removed: {$path}");
        }
    }

    private function clearCompiledLaravelFiles(): void
    {
        if ($this->dryRun) {
            $this->line('Would clear Laravel route, view and config compiled files.');
            return;
        }

        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
    }

    private function publishSiteVersion(): void
    {
        $version = 'public-'.now()->timestamp;

        if ($this->dryRun) {
            $this->line("Would publish public site version: {$version}");
            return;
        }

        File::ensureDirectoryExists(storage_path('app'));
        File::put($this->publicSiteVersionPath(), $version);
        $this->line("Published public site version: {$version}");
    }

    private function publicSiteVersionPath(): string
    {
        return storage_path('app/public-site-version.txt');
    }

    private function writeWhenChanged(string $path, string $contents, string $updated): void
    {
        if ($contents === $updated) {
            $this->line("Already up to date: {$path}");
            return;
        }

        if ($this->dryRun) {
            $this->line("Would update: {$path}");
            return;
        }

        File::put($path, $updated);
        $this->line("Updated: {$path}");
    }
}
