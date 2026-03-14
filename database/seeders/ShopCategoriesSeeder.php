<?php

namespace Database\Seeders;

use App\Models\ShopCategory;
use Illuminate\Database\Seeder;

class ShopCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Підсистеми', 'slug' => 'pods', 'sort_order' => 1],
            ['name' => 'Одноразки', 'slug' => 'disposables', 'sort_order' => 2],
            ['name' => 'Рідини', 'slug' => 'liquids', 'sort_order' => 3],
            ['name' => 'Картриджі', 'slug' => 'cartridges', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            ShopCategory::firstOrCreate(
                ['slug' => $item['slug']],
                ['name' => $item['name'], 'sort_order' => $item['sort_order']]
            );
        }
    }
}
