<?php

namespace Database\Seeders;

use App\Models\ShopCategory;
use Illuminate\Database\Seeder;

class ShopCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Pody', 'slug' => 'pods', 'sort_order' => 1],
            ['name' => 'Jednorazówki', 'slug' => 'disposables', 'sort_order' => 2],
            ['name' => 'Liquidy', 'slug' => 'liquids', 'sort_order' => 3],
            ['name' => 'Kartridże', 'slug' => 'cartridges', 'sort_order' => 4],
            ['name' => 'Snuś', 'slug' => 'snus', 'sort_order' => 5],
        ];

        foreach ($items as $item) {
            ShopCategory::updateOrCreate(
                ['slug' => $item['slug']],
                ['name' => $item['name'], 'sort_order' => $item['sort_order']]
            );
        }
    }
}
