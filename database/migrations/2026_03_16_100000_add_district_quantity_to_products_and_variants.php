<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'quantity_ursynow')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('quantity_ursynow')->default(0)->after('quantity');
                $table->unsignedInteger('quantity_praga')->default(0)->after('quantity_ursynow');
            });
        }
        if (Schema::hasTable('product_variants') && !Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unsignedInteger('quantity_ursynow')->default(0)->after('quantity');
                $table->unsignedInteger('quantity_praga')->default(0)->after('quantity_ursynow');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'quantity_ursynow')) {
            Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['quantity_ursynow', 'quantity_praga']));
        }
        if (Schema::hasColumn('product_variants', 'quantity_ursynow')) {
            Schema::table('product_variants', fn (Blueprint $t) => $t->dropColumn(['quantity_ursynow', 'quantity_praga']));
        }
    }
};
