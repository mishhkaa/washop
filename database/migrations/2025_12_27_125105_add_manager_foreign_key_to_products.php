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
        // Для SQLite потрібно пересоздати таблицю з foreign key
        if (DB::getDriverName() === 'sqlite') {
            // Перевіряємо, чи існує foreign key
            $foreignKeys = DB::select("PRAGMA foreign_key_list(products)");
            $hasManagerForeignKey = false;
            foreach ($foreignKeys as $fk) {
                if ($fk->from === 'manager_id' && $fk->table === 'users') {
                    $hasManagerForeignKey = true;
                    break;
                }
            }

            if (!$hasManagerForeignKey) {
                // Отримуємо дані з таблиці
                $products = DB::table('products')->get();

                // Пересоздаємо таблицю з foreign key
                DB::statement('DROP TABLE IF EXISTS products_backup');
                DB::statement('CREATE TABLE products_backup AS SELECT * FROM products');
                
                Schema::dropIfExists('products');
                
                Schema::create('products', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->decimal('purchase_price', 10, 2);
                    $table->integer('quantity')->default(0);
                    $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
                    $table->timestamps();
                });

                // Повертаємо дані
                foreach ($products as $product) {
                    // Перевіряємо, чи існує користувач з таким id перед вставкою
                    $managerExists = DB::table('users')->where('id', $product->manager_id)->exists();
                    DB::table('products')->insert([
                        'id' => $product->id,
                        'name' => $product->name,
                        'purchase_price' => $product->purchase_price,
                        'quantity' => $product->quantity ?? 0,
                        'manager_id' => $managerExists ? $product->manager_id : null,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                    ]);
                }

                DB::statement('DROP TABLE IF EXISTS products_backup');
            }
        } else {
            // Для інших БД просто додаємо foreign key
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('manager_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Для SQLite це складно повернути назад без втрати даних
            // Краще не робити rollback
        } else {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
            });
        }
    }
};

