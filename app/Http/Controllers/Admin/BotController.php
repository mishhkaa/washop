<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ShopCategory;
use Illuminate\Http\Request;

class BotController extends Controller
{
    /**
     * Розділ "Магазин / ТГ-бот": категорії, товари, замовлення з бота.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'categories');

        $shopCategories = ShopCategory::orderBy('sort_order')->orderBy('name')->get();
        $botProducts = Product::with('manager')
            ->where('available_in_bot', true)
            ->orderBy('name')
            ->get();
        $botOrders = Sale::with(['saleItems.product', 'manager'])
            ->where('source', 'bot')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.shop.index', compact('tab', 'shopCategories', 'botProducts', 'botOrders'));
    }
}
