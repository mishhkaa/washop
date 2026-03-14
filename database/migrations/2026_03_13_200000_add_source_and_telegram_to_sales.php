<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * source: crm = продаж з CRM, bot = замовлення з Telegram/сайту.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('source', 20)->default('crm')->after('profit_to_admin'); // crm | bot
            $table->string('telegram_user_id')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['source', 'telegram_user_id']);
        });
    }
};
