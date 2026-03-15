<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'delivery_pickup_day')) {
                $table->string('delivery_pickup_day', 255)->nullable()->after('delivery_pickup_district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'delivery_pickup_day')) {
                $table->dropColumn('delivery_pickup_day');
            }
        });
    }
};
