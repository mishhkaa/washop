<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Додати всі відсутні колонки в sales (якщо на проді не виконані окремі міграції).
     */
    public function up(): void
    {
        $add = function (string $col, callable $def) {
            if (Schema::hasColumn('sales', $col)) {
                return;
            }
            Schema::table('sales', function (Blueprint $table) use ($def) {
                $def($table);
            });
        };
        $add('sale_price', fn ($t) => $t->decimal('sale_price', 12, 2)->default(0));
        $add('profit', fn ($t) => $t->decimal('profit', 12, 2)->default(0));
        $add('is_combined', fn ($t) => $t->boolean('is_combined')->default(false));
        $add('profit_to_admin', fn ($t) => $t->boolean('profit_to_admin')->default(false));
        $add('source', fn ($t) => $t->string('source', 20)->default('crm'));
        $add('telegram_user_id', fn ($t) => $t->string('telegram_user_id', 100)->nullable());
        $add('telegram_username', fn ($t) => $t->string('telegram_username', 100)->nullable());
        $add('client_id', fn ($t) => $t->unsignedBigInteger('client_id')->nullable());
        $add('delivery_method', fn ($t) => $t->string('delivery_method', 64)->nullable());
        $add('delivery_paczkomat_code', fn ($t) => $t->string('delivery_paczkomat_code', 32)->nullable());
        $add('delivery_pickup_name', fn ($t) => $t->string('delivery_pickup_name', 255)->nullable());
        $add('delivery_pickup_phone', fn ($t) => $t->string('delivery_pickup_phone', 64)->nullable());
        $add('delivery_pickup_district', fn ($t) => $t->string('delivery_pickup_district', 255)->nullable());
    }

    public function down(): void
    {
        // не видаляємо колонки в down — вони могли існувати до цієї міграції
    }
};
