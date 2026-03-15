<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ShopCategory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('shop.layout', function ($view) {
            $raw = request()->session()->get('shop_cart', []);
            $cart = [];
            foreach ($raw as $k => $v) {
                if (is_array($v) && isset($v['product_id'], $v['quantity'])) {
                    $key = 'p' . (int) $v['product_id'] . '_v' . (int) ($v['variant_id'] ?? 0);
                    $cart[$key] = ['product_id' => (int) $v['product_id'], 'variant_id' => (int) ($v['variant_id'] ?? 0), 'quantity' => (int) $v['quantity']];
                } elseif (is_numeric($k) && (is_numeric($v) || is_array($v))) {
                    $qty = is_array($v) ? (int) ($v['quantity'] ?? 0) : (int) $v;
                    if ($qty > 0) {
                        $key = 'p' . (int) $k . '_v0';
                        $cart[$key] = ['product_id' => (int) $k, 'variant_id' => 0, 'quantity' => $qty];
                    }
                }
            }
            $cartItems = [];
            $cartCount = 0;
            if (!empty($cart)) {
                $productIds = array_unique(array_column($cart, 'product_id'));
                $products = Product::whereIn('id', $productIds)->with('variants')->get()->keyBy('id');
                foreach ($cart as $key => $entry) {
                    $productId = (int) ($entry['product_id'] ?? 0);
                    $variantId = (int) ($entry['variant_id'] ?? 0);
                    $qty = (int) ($entry['quantity'] ?? 0);
                    if ($qty <= 0 || !isset($products[$productId])) {
                        continue;
                    }
                    $product = $products[$productId];
                    $variant = $variantId > 0 ? $product->variants->firstWhere('id', $variantId) : null;
                    $cartItems[] = (object)[
                        'cart_key' => $key,
                        'product' => $product,
                        'variant' => $variant,
                        'variant_name' => $variant?->name,
                        'quantity' => $qty,
                    ];
                    $cartCount += $qty;
                }
            }
            $view->with('cartCount', $cartCount);
            $view->with('cartItems', $cartItems);
            $view->with('shopCategories', ShopCategory::orderBy('sort_order')->orderBy('name')->get());
        });
    }
}
