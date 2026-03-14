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
            $cart = request()->session()->get('shop_cart', []);
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $cartItems = [];
            foreach ($cart as $id => $qty) {
                if (isset($products[$id])) {
                    $cartItems[] = (object)['product' => $products[$id], 'quantity' => (int) $qty];
                }
            }
            $view->with('cartCount', array_sum($cart));
            $view->with('cartItems', $cartItems);
            $view->with('shopCategories', ShopCategory::orderBy('sort_order')->orderBy('name')->get());
        });
    }
}
