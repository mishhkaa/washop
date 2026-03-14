<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_user_id', 100)->nullable()->unique();
            $table->string('telegram_username', 100)->nullable();
            $table->string('name')->nullable();
            $table->string('phone', 50)->nullable();
            $table->decimal('cashback_balance', 12, 2)->default(0);
            $table->unsignedTinyInteger('cashback_percent')->default(5)->comment('% від суми замовлення');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
