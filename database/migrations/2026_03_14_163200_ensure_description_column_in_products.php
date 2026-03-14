<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Додає description до products, якщо колонки ще немає (SQLite не підтримує after()).
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
        // Не видаляємо — можуть бути дані
    }
};
