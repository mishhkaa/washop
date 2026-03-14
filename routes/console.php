<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:check-writable', function () {
    $conn = config('database.default');
    $path = config("database.connections.{$conn}.database");
    if ($conn !== 'sqlite' || empty($path)) {
        $path = database_path('database.sqlite');
    }
    if (!file_exists($path)) {
        $this->error('Database file not found: ' . $path);
        return 1;
    }
    $this->line('Database path: ' . $path);
    $this->line('Readable: ' . (is_readable($path) ? 'yes' : 'NO'));
    $this->line('Writable: ' . (is_writable($path) ? 'yes' : 'NO'));
    if (function_exists('posix_getpwuid') && fileowner($path) !== false) {
        $owner = posix_getpwuid(fileowner($path));
        $this->line('Owner: ' . ($owner['name'] ?? fileowner($path)));
    }
    $this->line('Permissions: ' . substr(sprintf('%o', fileperms($path)), -4));
    $dir = dirname($path);
    $this->line('Directory writable: ' . (is_writable($dir) ? 'yes' : 'NO'));
    if (!is_writable($path) || !is_writable($dir)) {
        $this->newLine();
        $this->warn('Fix: run on server (adjust user if PHP runs as www-data):');
        $this->line('  chmod 775 ' . $dir);
        $this->line('  chmod 664 ' . $path);
        $this->line('  chown -R $(whoami) ' . $dir . '   # or: chown -R www-data ' . $dir);
    }
    return 0;
})->purpose('Check if SQLite database is writable (fix "readonly database" on production)');
