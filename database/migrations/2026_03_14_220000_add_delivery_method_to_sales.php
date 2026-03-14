<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'delivery_method')) {
            return;
        }
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('delivery_method', 64)->nullable();
            });
        } else {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('delivery_method', 64)->nullable()->after('source');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('delivery_method');
        });
    }
};
