<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SplFileObject;

class Logs extends Command
{
    protected $signature = 'logs:watch
        {--type=all : all, requests, validation, unauthorized, warning or error}
        {--tail= : Number of recent lines to show before watching}
        {--search= : Extra text filter}
        {--file= : Log file path. Defaults to storage/logs/laravel.log}
        {--clear : Clear the log file before watching}
        {--stack : Show full stack traces}';

    protected $description = 'Watch Laravel logs with simple filters and colored output.';

    public function handle(): int
    {
        $path = $this->option('file') ?: storage_path('logs/laravel.log');
        $type = strtolower((string) $this->option('type'));
        $tail = $this->tailLines();
        $search = $this->option('search');
        $clear = (bool) $this->option('clear');
        $showStack = (bool) $this->option('stack');

        if (! in_array($type, ['all', 'requests', 'validation', 'unauthorized', 'warning', 'error'], true)) {
            $this->error('Invalid type. Use: all, requests, validation, unauthorized, warning or error.');

            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("Log file not found: {$path}");

            return self::FAILURE;
        }

        if ($clear) {
            file_put_contents($path, '');
            $tail = 0;

            $this->warn("Cleared log file: {$path}");
        }

        $this->info("Watching logs: {$path}");
        $this->line("Type: {$type}. Press Ctrl+C to stop.");
        $this->newLine();

        foreach ($this->tail($path, $tail) as $line) {
            $this->display($line, $type, $search, $showStack);
        }

        $position = filesize($path) ?: 0;

        while (true) {
            clearstatcache(true, $path);

            $size = filesize($path) ?: 0;

            if ($size < $position) {
                $position = 0;
            }

            if ($size > $position) {
                $handle = fopen($path, 'r');

                if ($handle === false) {
                    $this->warn("Could not read log file: {$path}");
                    sleep(1);

                    continue;
                }

                fseek($handle, $position);

                while (($line = fgets($handle)) !== false) {
                    $this->display($line, $type, $search, $showStack);
                }

                $position = ftell($handle) ?: $position;

                fclose($handle);
            }

            usleep(250_000);
        }
    }

    /**
     * @return array<int, string>
     */
    private function tailLines(): int
    {
        if ($this->option('tail') !== null) {
            return max(0, (int) $this->option('tail'));
        }

        return 0;
    }

    private function tail(string $path, int $lines): array
    {
        if ($lines === 0) {
            return [];
        }

        $file = new SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        $lastLine = $file->key();
        $start = max(0, $lastLine - $lines);
        $output = [];

        for ($line = $start; $line <= $lastLine; $line++) {
            $file->seek($line);
            $content = $file->current();

            if ($content !== false && trim($content) !== '') {
                $output[] = (string) $content;
            }
        }

        return $output;
    }

    private function display(string $line, string $type, ?string $search, bool $showStack): void
    {
        $line = $this->simplify($line, $showStack);

        if ($line === null) {
            return;
        }

        if (! $this->matches($line, $type, $search)) {
            return;
        }

        $line = rtrim($line);

        if (str_contains($line, '[WEB REQUEST VALIDATION FAILED]')) {
            $this->warn($line);

            return;
        }

        if (str_contains($line, '[WEB REQUEST UNAUTHORIZED]')) {
            $this->error($line);

            return;
        }

        if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:')) {
            $this->error($line);

            return;
        }

        if (str_contains($line, '.WARNING:')) {
            $this->warn($line);

            return;
        }

        if (str_contains($line, '[WEB REQUEST')) {
            $this->info($line);

            return;
        }

        $this->line($line);
    }

    private function simplify(string $line, bool $showStack): ?string
    {
        if ($showStack) {
            return $line;
        }

        $line = rtrim($line);

        if (! preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line)) {
            return null;
        }

        return preg_replace('/\s+\{"exception":.*$/', '', $line) ?? $line;
    }

    private function matches(string $line, string $type, ?string $search): bool
    {
        if ($search && ! str_contains(strtolower($line), strtolower($search))) {
            return false;
        }

        return match ($type) {
            'requests' => str_contains($line, '[WEB REQUEST'),
            'validation' => str_contains($line, '[REQUEST VALIDATION FAILED]'),
            'unauthorized' => str_contains($line, '[REQUEST UNAUTHORIZED]'),
            'warning' => str_contains($line, '.WARNING:'),
            'error' => str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:'),
            default => true,
        };
    }
}
