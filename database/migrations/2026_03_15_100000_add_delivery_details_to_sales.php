<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'delivery_paczkomat_code')) {
                $table->string('delivery_paczkomat_code', 32)->nullable()->after('delivery_method');
            }
            if (!Schema::hasColumn('sales', 'delivery_pickup_name')) {
                $table->string('delivery_pickup_name', 255)->nullable()->after('delivery_paczkomat_code');
            }
            if (!Schema::hasColumn('sales', 'delivery_pickup_phone')) {
                $table->string('delivery_pickup_phone', 64)->nullable()->after('delivery_pickup_name');
            }
            if (!Schema::hasColumn('sales', 'delivery_pickup_district')) {
                $table->string('delivery_pickup_district', 255)->nullable()->after('delivery_pickup_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_paczkomat_code',
                'delivery_pickup_name',
                'delivery_pickup_phone',
                'delivery_pickup_district',
            ]);
        });
    }
};
