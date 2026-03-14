<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class BotProductsSeeder extends Seeder
{
    /**
     * Товари з прикладу shop_site (головна) — додаються в CRM з позначкою "Товари з бота".
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Elfliq', 'purchase_price' => 50, 'quantity' => 20, 'shop_category' => 'liquids'],
            ['name' => 'Chaser', 'purchase_price' => 50, 'quantity' => 20, 'shop_category' => 'liquids'],
            ['name' => 'Vozol Prime', 'purchase_price' => 50, 'quantity' => 20, 'shop_category' => 'liquids'],
            ['name' => 'HQD', 'purchase_price' => 50, 'quantity' => 20, 'shop_category' => 'liquids'],
            ['name' => 'XROS Pod 0.6Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'XROS Pod 0.8Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'Oxva Xlim 0.6Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'Oxva Xlim 0.4Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'Ursa Nano 0.8Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'Ursa Nano 0.6Ω', 'purchase_price' => 25, 'quantity' => 30, 'shop_category' => 'cartridges'],
            ['name' => 'XROS 5 Mini', 'purchase_price' => 140, 'quantity' => 15, 'shop_category' => 'pods'],
            ['name' => 'XROS 4 Mini', 'purchase_price' => 130, 'quantity' => 15, 'shop_category' => 'pods'],
            ['name' => 'ElfBar IceKing', 'purchase_price' => 90, 'quantity' => 25, 'shop_category' => 'disposables'],
            ['name' => 'ElfBar NicKing', 'purchase_price' => 90, 'quantity' => 25, 'shop_category' => 'disposables'],
            ['name' => 'ElfBar Raya d3', 'purchase_price' => 90, 'quantity' => 25, 'shop_category' => 'disposables'],
            ['name' => 'ElfBar Gh33000pro', 'purchase_price' => 90, 'quantity' => 25, 'shop_category' => 'disposables'],
            ['name' => 'Elfbar MoonNight', 'purchase_price' => 80, 'quantity' => 25, 'shop_category' => 'disposables'],
            ['name' => 'ElfBar Bc20000', 'purchase_price' => 70, 'quantity' => 25, 'shop_category' => 'disposables'],
        ];

        foreach ($items as $item) {
            $category = $item['shop_category'] ?? null;
            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'purchase_price' => $item['purchase_price'],
                    'quantity' => $item['quantity'],
                    'manager_id' => null,
                    'available_in_bot' => true,
                    'shop_category' => $category,
                ]
            );
        }
    }
}
