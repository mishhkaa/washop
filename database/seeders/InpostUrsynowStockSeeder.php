<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class InpostUrsynowStockSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWithVariants('Elfliq', 50, 'liquids', [
            ['Sour Apple', 6],
            ['Sour Watermelon Gummy', 4],
            ['Pink Lemonade', 9],
            ['Green Grape Ross', 1],
            ['Strawberry Cherry Lemon', 8],
            ['Strawberry Snoow', 4],
            ['Apple Pear', 10],
            ['Raspberry Lychee', 3],
            ['Cherry Lemon Peach', 6],
            ['Apple Peach', 4],
            ['Cherry Cola', 4],
            ['Strawberry Raspberry Cherry Ice', 4],
            ['Strawberry Banana', 4],
            ['Peach Ice', 4],
            ['Pink Grapefruit', 3],
            ['Grape', 7],
            ['Grape Cherry', 5],
            ['Blueberry Sour Raspberry', 6],
            ['Jasmine Raspberry', 7],
            ['Pink Lemonade Soda', 6],
            ['Blue Razz Lemonade', 1],
            ['Blueberry Raspberry Pomegranate', 4],
            ['Cherry', 4],
            ['Watermelon Cherry', 3],
            ['Elfjack', 2],
            ['Pina Colada', 2],
            ['Pineapple Colada', 2],
            ['Strawberry Kiwi', 5],
            ['Kiwi Passion Fruit Guava', 4],
            ['Double Apple', 3],
            ['Spear Mint', 3],
            ['Blueberry', 2],
        ]);

        $this->seedWithVariants('Nova', 50, 'liquids', [
            ['Spear Mint', 6],
            ['Watermelon Lychee', 1],
        ]);

        $this->seedWithVariants('Chaser (My Mint)', 50, 'liquids', [
            ['Sweet Mint', 8],
            ['Menthol', 10],
            ['Cool Mint', 11],
            ['Spear Mint', 6],
            ['Cranberry Mint', 9],
        ]);

        $this->seedWithVariants('Chaser Special (Berry)', 50, 'liquids', [
            ['Strawberry Jelly', 11],
            ['Mystery One', 7],
            ['BlackBerry', 10],
            ['Fall Tea', 8],
            ['Energy Cherry', 16],
        ]);

        $this->seedWithVariants('Chaser Lux Ultra', 50, 'liquids', [
            ['Lychee Passion', 1],
            ['Blueberry Mint', 11],
            ['Berry Needles', 14],
        ]);

        $this->seedWithVariants('Chaser Mix Ultra', 50, 'liquids', [
            ['Cherry Tobacco', 2],
            ['Cranberry Cactus', 3],
        ]);

        $this->seedWithVariants('Chaser Ultra', 50, 'liquids', [
            ['Banan', 3],
            ['Lychee', 3],
            ['Mint', 10],
        ]);

        $this->seedWithVariants('Chaser', 50, 'liquids', [
            ['Peach', 5],
        ]);

        $this->seedWithVariants('Chaser Christmas', 50, 'liquids', [
            ['Christmas Tree', 1],
            ['Pumpkin Latte', 1],
        ]);

        $this->seedWithVariants('VOZOL', 50, 'liquids', [
            ['Watermelon Bubble Gum', 2],
            ['Strawberry Ice Cream', 1],
            ['Purple Candy', 1],
        ]);

        $this->seedWithVariants('HQD', 50, 'liquids', [
            ['Blueberry Sour Raspberry', 15],
            ['Blueberry Raspberry', 4],
        ]);

        $this->seedWithVariants('ELFBAR GH33000PRO', 90, 'disposables', [
            ['Pine Needles', 3],
            ['Apple Kiwi Ice', 1],
            ['Kiwi Pineapple Peach', 1],
            ['Grapefruit Passion Guava', 2],
            ['Mountain Mint', 2],
        ]);

        $this->seedWithVariants('ELFBAR Nic King 30000', 90, 'disposables', [
            ['Sour Apple Watermelon', 1],
            ['Watermelon Cherry', 2],
            ['Pomegranate Burst', 1],
            ['Sour Cherry Candy', 2],
            ['Sour Strawberry Dragonfruit', 2],
            ['Grapefruit Green Tea', 1],
        ]);

        $this->seedWithVariants('ELFBAR RAYA D3 25000', 90, 'disposables', [
            ['Kiwi Passion Fruit Guava', 1],
            ['Strawberry Grape', 3],
            ['Watermelon Lemon', 1],
            ['Alpine Mint', 4],
            ['Apple Peach', 2],
            ['Peach Ice', 2],
            ['Blueberry Ice', 1],
            ['Kiwi Pineapple Ice', 1],
            ['Blueberry Raspberry', 1],
            ['Grape Cherry', 1],
            ['VMT', 1],
            ['Pineapple Mango Ice', 1],
        ]);

        $this->seedWithVariants('ELFBAR GH23000', 90, 'disposables', [
            ['Cherry Cola', 1],
            ['Strawberry Watermelon Bubble Gum', 2],
            ['Jasmine Raspberry', 1],
            ['Apple Pear', 2],
            ['Blue Razz Ice', 2],
        ]);

        $this->seedWithVariants('ELFBAR BC20000', 70, 'disposables', [
            ['Lemon Lime', 3],
            ['Orange Pomegranate Cranberry', 2],
            ['Watermelon Sour Peach', 1],
        ]);

        $this->seedWithVariants('ELFBAR Moonnight 40000K', 90, 'disposables', [
            ['Miami Mint', 2],
        ]);

        $this->seedWithVariants('ELFBAR Trio 40000K', 90, 'disposables', [
            ['Sour Apple Ice', 2],
        ]);

        $this->seedWithVariants('ELFBAR Ice King 30000K', 90, 'disposables', [
            ['Blue Razz Ice', 5],
            ['Miami Mint', 1],
            ['Cherry Pomegranate Cranberry', 1],
            ['Pink Lemonade', 1],
            ['Kiwi Passion Fruit Guava', 1],
            ['Elf Bull', 1],
            ['Strawberry Watermelon', 1],
        ]);

        $this->seedWithVariants('CUBA снюс', 20, 'snus', [
            ['Apple Juice', 3],
            ['Cherry', 2],
            ['Forest Berries', 3],
            ['Blueberry', 3],
        ]);

        $this->seedWithVariants('XROS 5 Mini', 140, 'pods', [
            ['Rose Red', 4],
            ['Flowing Green', 1],
            ['Sky Blue', 1],
        ]);

        $this->seedWithVariants('XROS 4 Mini', 130, 'pods', [
            ['Camo Silver', 1],
            ['Space Grey', 1],
            ['Ice Pink', 1],
            ['Camo Yellow', 1],
            ['Ice Green', 1],
        ]);

        $this->seedWithVariants('XROS 3 Mini', 120, 'pods', [
            ['Lemon Yellow', 1],
        ]);

        $this->seedSimple('XROS Pod 1.0Ω', 25, 'cartridges', 5);
        $this->seedSimple('XROS Pod 0.6Ω', 25, 'cartridges', 25);
        $this->seedSimple('XROS Pod 0.8Ω', 25, 'cartridges', 72);
        $this->seedSimple('Ursa Nano 0.8Ω', 25, 'cartridges', 14);
        $this->seedSimple('Ursa Nano 0.6Ω', 25, 'cartridges', 5);
        $this->seedSimple('Oxva Xlim 0.8Ω', 25, 'cartridges', 4);
        $this->seedSimple('Oxva Xlim 0.6Ω', 25, 'cartridges', 2);
        $this->seedSimple('Oxva Xlim 0.4Ω', 25, 'cartridges', 5);
    }

    private function seedSimple(string $name, int $price, string $category, int $qty): void
    {
        $this->upsertProductCaseInsensitive($name, [
            'purchase_price' => $price,
            'shop_category' => $category,
            'available_in_bot' => true,
            'quantity' => $qty,
            'quantity_praga' => $qty,
            'quantity_ursynow' => $qty,
        ]);
    }

    private function upsertProductCaseInsensitive(string $name, array $attributes): Product
    {
        $existing = Product::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->first();

        if ($existing) {
            $existing->fill(array_merge(['name' => $name], $attributes));
            $existing->save();
            return $existing;
        }

        return Product::create(
            array_merge(
                [
                    'name' => $name,
                    'manager_id' => null,
                ],
                $attributes
            )
        );
    }

    private function seedWithVariants(string $name, int $price, string $category, array $variants): void
    {
        $total = array_sum(array_map(fn (array $item) => (int) $item[1], $variants));

        $product = $this->upsertProductCaseInsensitive($name, [
            'purchase_price' => $price,
            'shop_category' => $category,
            'available_in_bot' => true,
            'quantity' => $total,
            'quantity_praga' => $total,
            'quantity_ursynow' => $total,
        ]);

        foreach (array_values($variants) as $index => $variant) {
            $variantName = (string) $variant[0];
            $qty = (int) $variant[1];

            $existingVariant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($variantName))])
                ->first();

            if ($existingVariant) {
                $existingVariant->fill([
                    'name' => $variantName,
                    'quantity' => $qty,
                    'quantity_praga' => $qty,
                    'quantity_ursynow' => $qty,
                    'sort_order' => $index,
                ]);
                $existingVariant->save();
            } else {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $variantName,
                    'quantity' => $qty,
                    'quantity_praga' => $qty,
                    'quantity_ursynow' => $qty,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
