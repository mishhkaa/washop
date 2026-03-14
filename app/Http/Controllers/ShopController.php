<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopCategory;
use App\Models\User;
use App\Services\TelegramOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('available_in_bot', true)->where('quantity', '>', 0);

        if ($request->filled('category')) {
            $query->where('shop_category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->get('sort', 'default');
        switch ($sort) {
            case 'name-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price-asc':
                $query->orderBy('purchase_price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('purchase_price', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $products = $query->get();

        $categories = ShopCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function addToCart(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'nullable|integer|min:1']);
        $id = (int) $request->product_id;
        $qty = (int) ($request->quantity ?? 1);
        $product = Product::where('id', $id)->where('available_in_bot', true)->where('quantity', '>=', $qty)->firstOrFail();
        $cart = $request->session()->get('shop_cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $qty;
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum($cart)]);
        }
        return redirect()->route('shop.cart')->with('success', __('Added to cart'));
    }

    public function updateCart(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:0']);
        $id = (int) $request->product_id;
        $qty = (int) $request->quantity;
        $cart = $request->session()->get('shop_cart', []);
        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $qty;
        }
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum($cart)]);
        }
        return redirect()->back()->with('open_cart', true);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $cart = $request->session()->get('shop_cart', []);
        unset($cart[(int) $request->product_id]);
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum($cart)]);
        }
        return redirect()->back()->with('open_cart', true);
    }

    public function checkoutForm(Request $request)
    {
        $cart = $request->session()->get('shop_cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.home')->with('message', __('Cart is empty'));
        }
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $items = [];
        foreach ($cart as $id => $qty) {
            if (isset($products[$id])) {
                $items[] = (object)['product' => $products[$id], 'quantity' => (int) $qty];
            }
        }
        return view('shop.checkout', compact('items'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'delivery_method' => 'required|in:paczkomat,osobisty_odbior',
        ], [
            'delivery_method.required' => __('Please choose delivery method'),
            'delivery_method.in' => __('Please choose delivery method'),
        ]);
        $cart = $request->session()->get('shop_cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.home')->with('message', __('Cart is empty'));
        }
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->where('available_in_bot', true)->get()->keyBy('id');
        $orderItems = [];
        foreach ($cart as $id => $qty) {
            if (!isset($products[$id]) || ($products[$id]->quantity ?? 0) < $qty) {
                return back()->with('error', __('Insufficient product'));
            }
            $orderItems[] = ['product' => $products[$id], 'quantity' => (int) $qty];
        }

        try {
            $admin = User::where('role', 'admin')->first();
            $managerId = $admin ? $admin->id : null;
            if (!$managerId) {
                $managerId = User::first()?->id;
            }

            $sale = DB::transaction(function () use ($orderItems, $managerId, $request) {
                $totalProfit = 0;
                $orderTotal = 0;
                $saleItemsData = [];
                foreach ($orderItems as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product']->id);
                    $qty = $item['quantity'];
                    $salePrice = (float) $product->purchase_price;
                    $product->decrement('quantity', $qty);
                    $profit = ($salePrice - ($product->purchase_price ?? 0)) * $qty;
                    $totalProfit += $profit;
                    $orderTotal += $salePrice * $qty;
                    $saleItemsData[] = [
                        'product' => $product,
                        'quantity' => $qty,
                        'sale_price' => $salePrice,
                        'profit' => $profit,
                    ];
                }
                $client = Client::findOrCreateByTelegram(
                    $request->input('telegram_user_id'),
                    $request->input('telegram_username')
                );
                $saleData = [
                    'manager_id' => $managerId,
                    'client_id' => $client?->id,
                    'product_id' => null,
                    'quantity' => 0,
                    'sale_price' => 0,
                    'profit' => $totalProfit,
                    'is_combined' => true,
                    'profit_to_admin' => true,
                    'source' => 'bot',
                    'telegram_user_id' => $request->input('telegram_user_id'),
                    'telegram_username' => $request->input('telegram_username'),
                ];
                if (Schema::hasColumn('sales', 'delivery_method')) {
                    $saleData['delivery_method'] = $request->input('delivery_method');
                }
                $sale = Sale::create($saleData);
                foreach ($saleItemsData as $data) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $data['product']->id,
                        'quantity' => $data['quantity'],
                        'sale_price' => $data['sale_price'],
                        'profit' => $data['profit'],
                    ]);
                }
                if ($client && $orderTotal > 0) {
                    $client->accrueCashback($orderTotal);
                }
                return $sale;
            });

            try {
                TelegramOrderNotification::sendOrderNotification($sale->load('saleItems.product'));
            } catch (\Throwable $e) {
                // не ламати чекаут при помилці відправки в Telegram
            }

            $request->session()->forget('shop_cart');
            return redirect()->route('shop.home')->with('success', __('Order :id placed thanks', ['id' => $sale->id]));
        } catch (\Throwable $e) {
            return back()->with('error', __('Error') . ': ' . $e->getMessage());
        }
    }
}
