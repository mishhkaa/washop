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
        // Таблиця вже існує з попередньої міграції, тому просто пропускаємо
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->date('payment_date');
                $table->date('period_from');
                $table->date('period_to');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            // Якщо таблиця існує, додаємо тільки відсутні поля
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'manager_id')) {
                    $table->foreignId('manager_id')->constrained('users')->onDelete('cascade')->after('id');
                }
                if (!Schema::hasColumn('payments', 'amount')) {
                    $table->decimal('amount', 10, 2)->after('manager_id');
                }
                if (!Schema::hasColumn('payments', 'payment_date')) {
                    $table->date('payment_date')->after('amount');
                }
                if (!Schema::hasColumn('payments', 'period_from')) {
                    $table->date('period_from')->after('payment_date');
                }
                if (!Schema::hasColumn('payments', 'period_to')) {
                    $table->date('period_to')->after('period_from');
                }
                if (!Schema::hasColumn('payments', 'notes')) {
                    $table->text('notes')->nullable()->after('period_to');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
