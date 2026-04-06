<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeduplicateProductsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->mergeAliasGroups();

            $groups = DB::table('products')
                ->selectRaw('LOWER(TRIM(name)) as normalized_name, COUNT(*) as total')
                ->groupBy('normalized_name')
                ->having('total', '>', 1)
                ->pluck('normalized_name');

            foreach ($groups as $normalizedName) {
                $products = Product::query()
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                    ->orderBy('id')
                    ->get();

                if ($products->count() < 2) {
                    continue;
                }

                $keeper = $products->first();

                foreach ($products->slice(1) as $duplicate) {
                    $this->mergeProductInto($keeper, $duplicate);
                }

                $this->recalculateProductStockFromVariants($keeper->fresh());
            }

            Product::query()->select('id')->chunkById(200, function ($products): void {
                foreach ($products as $product) {
                    $this->dedupeVariantsInsideProduct((int) $product->id);
                }
            });
        });
    }

    private function mergeAliasGroups(): void
    {
        $aliasGroups = [
            'CUBA' => ['CUBA', 'CUBA CHOC', 'CUBA снюс'],
        ];

        foreach ($aliasGroups as $canonicalName => $aliases) {
            $normalizedAliases = array_map(
                fn (string $name) => mb_strtolower(trim($name)),
                $aliases
            );

            $products = Product::query()
                ->whereIn(DB::raw('LOWER(TRIM(name))'), $normalizedAliases)
                ->orderBy('id')
                ->get();

            if ($products->count() < 2) {
                continue;
            }

            $keeper = $products->firstWhere(
                fn (Product $product) => mb_strtolower(trim($product->name)) === mb_strtolower(trim($canonicalName))
            ) ?? $products->first();

            if ($keeper->name !== $canonicalName) {
                $keeper->name = $canonicalName;
                $keeper->save();
            }

            foreach ($products as $product) {
                if ($product->id === $keeper->id) {
                    continue;
                }
                $this->mergeProductInto($keeper, $product);
            }

            $this->recalculateProductStockFromVariants($keeper->fresh());
        }
    }

    private function mergeProductInto(Product $keeper, Product $duplicate): void
    {
        DB::table('sale_items')->where('product_id', $duplicate->id)->update(['product_id' => $keeper->id]);
        DB::table('sales')->where('product_id', $duplicate->id)->update(['product_id' => $keeper->id]);

        $this->mergeVariants($keeper, $duplicate);
        $duplicate->delete();
    }

    private function mergeVariants(Product $keeper, Product $duplicate): void
    {
        $duplicateVariants = ProductVariant::query()
            ->where('product_id', $duplicate->id)
            ->orderBy('id')
            ->get();

        foreach ($duplicateVariants as $duplicateVariant) {
            $targetVariant = ProductVariant::query()
                ->where('product_id', $keeper->id)
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($duplicateVariant->name))])
                ->first();

            if ($targetVariant) {
                DB::table('sale_items')
                    ->where('product_variant_id', $duplicateVariant->id)
                    ->update(['product_variant_id' => $targetVariant->id]);

                $targetVariant->update([
                    'quantity' => max((int) $targetVariant->quantity, (int) $duplicateVariant->quantity),
                    'quantity_ursynow' => max((int) $targetVariant->quantity_ursynow, (int) $duplicateVariant->quantity_ursynow),
                    'quantity_praga' => max((int) $targetVariant->quantity_praga, (int) $duplicateVariant->quantity_praga),
                    'sort_order' => min((int) $targetVariant->sort_order, (int) $duplicateVariant->sort_order),
                ]);

                $duplicateVariant->delete();
                continue;
            }

            $duplicateVariant->update(['product_id' => $keeper->id]);
        }
    }

    private function recalculateProductStockFromVariants(Product $product): void
    {
        $variants = ProductVariant::query()->where('product_id', $product->id)->get();

        if ($variants->isEmpty()) {
            return;
        }

        $product->update([
            'quantity' => (int) $variants->sum('quantity'),
            'quantity_ursynow' => (int) $variants->sum('quantity_ursynow'),
            'quantity_praga' => (int) $variants->sum('quantity_praga'),
        ]);
    }

    private function dedupeVariantsInsideProduct(int $productId): void
    {
        $groups = ProductVariant::query()
            ->where('product_id', $productId)
            ->selectRaw('LOWER(TRIM(name)) as normalized_name, COUNT(*) as total')
            ->groupBy('normalized_name')
            ->having('total', '>', 1)
            ->pluck('normalized_name');

        foreach ($groups as $normalizedName) {
            $variants = ProductVariant::query()
                ->where('product_id', $productId)
                ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                ->orderBy('id')
                ->get();

            $keeper = $variants->first();
            if (!$keeper) {
                continue;
            }

            foreach ($variants->slice(1) as $duplicate) {
                DB::table('sale_items')
                    ->where('product_variant_id', $duplicate->id)
                    ->update(['product_variant_id' => $keeper->id]);

                $keeper->update([
                    'quantity' => max((int) $keeper->quantity, (int) $duplicate->quantity),
                    'quantity_ursynow' => max((int) $keeper->quantity_ursynow, (int) $duplicate->quantity_ursynow),
                    'quantity_praga' => max((int) $keeper->quantity_praga, (int) $duplicate->quantity_praga),
                    'sort_order' => min((int) $keeper->sort_order, (int) $duplicate->sort_order),
                ]);

                $duplicate->delete();
                $keeper->refresh();
            }
        }

        $product = Product::query()->find($productId);
        if ($product) {
            $this->recalculateProductStockFromVariants($product);
        }
    }

}
