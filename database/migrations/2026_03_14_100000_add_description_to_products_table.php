<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SQLite не підтримує AFTER — додаємо колонку без after().
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'description')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE products ADD COLUMN description TEXT NULL');
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite не підтримує DROP COLUMN в старих версіях — лишаємо колонку
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
