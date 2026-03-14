<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('salary_type')->default('auto')->after('role'); // 'auto' або 'manual'
            $table->decimal('salary_amount', 10, 2)->nullable()->after('salary_type'); // Сума зарплати якщо manual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'salary_amount']);
        });
    }
};
