<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Виправляємо foreign key для manager_id в таблиці sales
        // Він повинен посилатися на таблицю users, а не managers
        
        if (DB::getDriverName() === 'sqlite') {
            // Для SQLite потрібно пересоздати таблицю
            DB::statement('PRAGMA foreign_keys=off;');
            
            DB::statement('
                CREATE TABLE sales_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_id INTEGER NOT NULL,
                    manager_id INTEGER NOT NULL,
                    quantity INTEGER NOT NULL,
                    sale_price NUMERIC NOT NULL,
                    profit NUMERIC NOT NULL,
                    created_at DATETIME,
                    updated_at DATETIME,
                    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE,
                    FOREIGN KEY(manager_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ');
            
            DB::statement('INSERT INTO sales_new SELECT * FROM sales;');
            DB::statement('DROP TABLE sales;');
            DB::statement('ALTER TABLE sales_new RENAME TO sales;');
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // Для інших БД (MySQL, PostgreSQL) просто видаляємо і додаємо foreign key
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
            });
            
            Schema::table('sales', function (Blueprint $table) {
                $table->foreign('manager_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Не можемо повернути назад, бо таблиця managers не існує
        // Якщо потрібно, можна буде створити окрему міграцію
    }
};
