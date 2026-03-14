<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Менеджер бачить тільки свої товари або товари без призначення (для всіх)
        $products = Product::where(function($query) {
            $query->where('manager_id', auth()->id())
                  ->orWhereNull('manager_id');
        })
        ->with('manager')
        ->get();

        return view('manager.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        // Перевірка, чи товар належить менеджеру або доступний всім
        if ($product->manager_id !== null && $product->manager_id !== auth()->id()) {
            abort(403, 'Ви не маєте доступу до цього товару');
        }

        return view('manager.products.show', compact('product'));
    }
}

