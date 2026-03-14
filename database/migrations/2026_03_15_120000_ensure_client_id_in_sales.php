<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Додати client_id до sales, якщо колонки немає (напр. міграція add_client_id не виконалась на проді).
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'client_id')) {
            return;
        }
        $driver = Schema::getConnection()->getDriverName();
        Schema::table('sales', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->unsignedBigInteger('client_id')->nullable();
            } else {
                $table->foreignId('client_id')->nullable()->after('manager_id')->constrained('clients')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('sales', 'client_id')) {
            return;
        }
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['client_id']);
            } else {
                $table->dropColumn('client_id');
            }
        });
    }
};
