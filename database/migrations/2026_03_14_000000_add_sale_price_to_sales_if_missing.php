<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds sale_price (and profit if missing) to sales when the table still has "price" from the original migration.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'sale_price')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('sale_price', 10, 2)->default(0);
            });
            if (Schema::hasColumn('sales', 'price')) {
                DB::table('sales')->update(['sale_price' => DB::raw('price')]);
            }
        }

        if (! Schema::hasColumn('sales', 'profit')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('profit', 10, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible; leave columns in place
    }
};
