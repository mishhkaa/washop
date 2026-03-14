<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Зробити sales.product_id nullable (для комбінованих замовлень). SQLite не підтримує ALTER COLUMN — перестворюємо таблицю.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }
        if (!Schema::hasTable('sales')) {
            return;
        }
        $cols = DB::select("PRAGMA table_info('sales')");
        $productIdNullable = false;
        foreach ($cols as $c) {
            if ($c->name === 'product_id') {
                if ((int) $c->notnull === 0) {
                    $productIdNullable = true;
                }
                break;
            }
        }
        if ($productIdNullable) {
            return;
        }

        $columnNames = array_map(fn ($c) => $c->name, $cols);
        $colDefs = [];
        foreach ($cols as $c) {
            $type = $c->type ?: 'INTEGER';
            $null = ($c->name === 'product_id') ? ' NULL' : ((int) $c->notnull === 1 ? ' NOT NULL' : ' NULL');
            $default = $c->dflt_value !== null && $c->dflt_value !== '' ? ' DEFAULT ' . (is_numeric($c->dflt_value) ? $c->dflt_value : "'" . str_replace("'", "''", $c->dflt_value) . "'") : '';
            $pk = (int) $c->pk === 1 ? ' PRIMARY KEY AUTOINCREMENT' : '';
            $colDefs[] = $c->name . ' ' . $type . $null . $default . $pk;
        }
        $createSql = 'CREATE TABLE sales_new (' . implode(', ', $colDefs) . ')';
        DB::statement($createSql);

        $colList = implode(', ', $columnNames);
        DB::statement("INSERT INTO sales_new ({$colList}) SELECT {$colList} FROM sales");

        DB::statement('DROP TABLE sales');
        DB::statement('ALTER TABLE sales_new RENAME TO sales');
    }

    public function down(): void
    {
    }
};
