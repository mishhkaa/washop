<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class BotProductController extends Controller
{
    /**
     * Список товарів для бота / shop_site (тільки available_in_bot = true).
     * GET /api/bot/products
     */
    public function index(): JsonResponse
    {
        $products = Product::where('available_in_bot', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'purchase_price', 'quantity', 'shop_category'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->display_name,
                    'price' => (float) $p->purchase_price, // або окреме поле sale_price якщо додасте
                    'quantity' => (int) $p->quantity,
                ];
            });

        return response()->json(['data' => $products]);
    }
}
