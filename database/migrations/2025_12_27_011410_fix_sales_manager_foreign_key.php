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
            // Якщо sales вже має sale_price — міграція вже застосована (або через цю, або через add_sale_price_to_sales_if_missing)
            if (Schema::hasColumn('sales', 'sale_price')) {
                return;
            }
            // Видаляємо залишок від попереднього незавершеного запуску
            DB::statement('DROP TABLE IF EXISTS sales_new');
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
            // Явний список колонок: sales має price, sales_new — sale_price і profit
            DB::statement('INSERT INTO sales_new (id, product_id, manager_id, quantity, sale_price, profit, created_at, updated_at) SELECT id, product_id, manager_id, quantity, price, 0, created_at, updated_at FROM sales');
            DB::statement('DROP TABLE sales;');
            DB::statement('ALTER TABLE sales_new RENAME TO sales;');

            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // Для інших БД (MySQL, PostgreSQL) просто видаляємо і додаємо foreign key
            try {
                Schema::table('sales', function (Blueprint $table) {
                    $table->dropForeign(['manager_id']);
                });
            } catch (\Throwable $e) {
                // FK вже правильний або відсутній
            }
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
