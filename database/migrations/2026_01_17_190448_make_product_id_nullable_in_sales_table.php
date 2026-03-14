<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Видаляємо foreign key constraint
            $table->dropForeign(['product_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            // Змінюємо колонку на nullable
            $table->foreignId('product_id')->nullable()->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            // Додаємо foreign key constraint назад
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Видаляємо foreign key constraint
            $table->dropForeign(['product_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            // Змінюємо колонку на NOT NULL
            $table->foreignId('product_id')->nullable(false)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            // Додаємо foreign key constraint назад
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
