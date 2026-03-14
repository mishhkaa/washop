<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Адмін для першого входу в CRM (зміни пароль після деплою)
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Тестовий менеджер
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'manager',
        ]);
    }
}
