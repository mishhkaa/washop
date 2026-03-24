<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DevFullDatabaseSeeder extends Seeder
{
    /**
     * Rebuild the entire DB from current dev SQLite snapshot.
     *
     * Usage:
     * php artisan db:seed --class=DevFullDatabaseSeeder --force
     */
    public function run(): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'sqlite') {
            throw new RuntimeException('DevFullDatabaseSeeder supports sqlite only.');
        }

        $snapshotPath = database_path('seeders/sql/dev_full_snapshot.sql');
        if (!is_file($snapshotPath)) {
            throw new RuntimeException("Snapshot file not found: {$snapshotPath}");
        }

        // Drop all user tables so snapshot can recreate schema and data cleanly.
        $tables = $connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        foreach ($tables as $table) {
            $name = $table->name ?? null;
            if ($name) {
                $connection->statement('DROP TABLE IF EXISTS "' . str_replace('"', '""', $name) . '"');
            }
        }

        $sql = file_get_contents($snapshotPath);
        if ($sql === false) {
            throw new RuntimeException("Unable to read snapshot file: {$snapshotPath}");
        }

        $connection->unprepared($sql);
    }
}

