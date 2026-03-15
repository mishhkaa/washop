<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductVariant;
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
    /** Нормалізує кошик до формату [ key => [product_id, variant_id, quantity] ] */
    private function normalizeCart(array $cart): array
    {
        $out = [];
        foreach ($cart as $key => $val) {
            if (is_array($val) && isset($val['product_id'], $val['quantity'])) {
                $k = 'p' . (int) $val['product_id'] . '_v' . (int) ($val['variant_id'] ?? 0);
                $out[$k] = ['product_id' => (int) $val['product_id'], 'variant_id' => (int) ($val['variant_id'] ?? 0), 'quantity' => (int) $val['quantity']];
            } elseif (is_numeric($key) && (is_numeric($val) || is_array($val))) {
                $qty = is_array($val) ? (int) ($val['quantity'] ?? 0) : (int) $val;
                if ($qty > 0) {
                    $k = 'p' . (int) $key . '_v0';
                    $out[$k] = ['product_id' => (int) $key, 'variant_id' => 0, 'quantity' => $qty];
                }
            }
        }
        return $out;
    }

    public function index(Request $request)
    {
        $query = Product::where('available_in_bot', true)
            ->where(function ($q) {
                $q->where('quantity', '>', 0)
                    ->orWhereHas('variants', fn ($v) => $v->where('quantity', '>', 0));
            })
            ->with('variants');

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
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:1',
        ]);
        $productId = (int) $request->product_id;
        $variantId = $request->has('variant_id') ? (int) $request->variant_id : 0;
        $qty = (int) ($request->quantity ?? 1);
        $product = Product::where('id', $productId)->where('available_in_bot', true)->with('variants')->firstOrFail();

        if ($product->hasVariants()) {
            if ($variantId <= 0) {
                return back()->with('error', __('Choose flavor'));
            }
            $variant = $product->variants()->where('id', $variantId)->where('quantity', '>=', $qty)->firstOrFail();
        } else {
            if ($variantId !== 0) {
                return back()->with('error', __('Error'));
            }
            if (($product->quantity ?? 0) < $qty) {
                return back()->with('error', __('Insufficient product'));
            }
        }

        $cart = $request->session()->get('shop_cart', []);
        $key = 'p' . $productId . '_v' . $variantId;
        $cart[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => ($cart[$key]['quantity'] ?? 0) + $qty,
        ];
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum(array_column($cart, 'quantity'))]);
        }
        return redirect()->route('shop.home')->with('success', __('Added to cart'));
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string|max:64',
            'quantity' => 'required|integer|min:0',
        ]);
        $key = $request->cart_key;
        $qty = (int) $request->quantity;
        $cart = $request->session()->get('shop_cart', []);
        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $qty;
            }
        }
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum(array_column($cart, 'quantity'))]);
        }
        return redirect()->back()->with('open_cart', true);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate(['cart_key' => 'required|string|max:64']);
        $cart = $request->session()->get('shop_cart', []);
        unset($cart[$request->cart_key]);
        $request->session()->put('shop_cart', $cart);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'count' => array_sum(array_column($cart, 'quantity'))]);
        }
        return redirect()->back()->with('open_cart', true);
    }

    public function setTelegramSession(Request $request)
    {
        $request->validate([
            'telegram_user_id' => 'nullable|string|max:100',
            'telegram_username' => 'nullable|string|max:100',
        ]);
        if ($request->filled('telegram_user_id') || $request->filled('telegram_username')) {
            session([
                'shop_telegram_user_id' => $request->input('telegram_user_id'),
                'shop_telegram_username' => $request->input('telegram_username'),
            ]);
        }
        return response()->json(['ok' => true]);
    }

    public function checkoutForm(Request $request)
    {
        $cart = $this->normalizeCart($request->session()->get('shop_cart', []));
        if (empty($cart)) {
            return redirect()->route('shop.home')->with('message', __('Cart is empty'));
        }
        $productIds = array_unique(array_column($cart, 'product_id'));
        $products = Product::whereIn('id', $productIds)->with('variants')->get()->keyBy('id');
        $items = [];
        $orderTotal = 0;
        foreach ($cart as $key => $entry) {
            $productId = (int) ($entry['product_id'] ?? 0);
            $variantId = (int) ($entry['variant_id'] ?? 0);
            $qty = (int) ($entry['quantity'] ?? 0);
            if ($qty <= 0 || !isset($products[$productId])) {
                continue;
            }
            $product = $products[$productId];
            $variant = null;
            $variantName = null;
            if ($variantId > 0) {
                $variant = $product->variants->firstWhere('id', $variantId);
                $variantName = $variant?->name;
            }
            $price = (float) $product->purchase_price;
            $items[] = (object)[
                'cart_key' => $key,
                'product' => $product,
                'variant' => $variant,
                'variant_name' => $variantName,
                'quantity' => $qty,
            ];
            $orderTotal += $price * $qty;
        }
        $client = null;
        $tid = $request->input('telegram_user_id') ?: session('shop_telegram_user_id');
        $tun = $request->input('telegram_username') ?: session('shop_telegram_username');
        if ($tid || $tun) {
            $client = Client::findOrCreateByTelegram($tid, $tun);
        }
        return view('shop.checkout', compact('items', 'client', 'orderTotal'));
    }

    public function checkout(Request $request)
    {
        $rules = [
            'delivery_method' => 'required|in:paczkomat,osobisty_odbior',
        ];
        $messages = [
            'delivery_method.required' => __('Please choose delivery method'),
            'delivery_method.in' => __('Please choose delivery method'),
        ];
        if ($request->input('delivery_method') === 'paczkomat') {
            $rules['delivery_paczkomat_code'] = 'required|string|max:32';
            $messages['delivery_paczkomat_code.required'] = __('Required for Paczkomat');
        }
        if ($request->input('delivery_method') === 'osobisty_odbior') {
            $rules['delivery_pickup_name'] = 'required|string|max:255';
            $rules['delivery_pickup_phone'] = 'required|string|max:64';
            $rules['delivery_pickup_district'] = 'required|string|max:255';
            $rules['delivery_pickup_day'] = 'required|string|max:255';
            $messages['delivery_pickup_name.required'] = __('Required for pickup');
            $messages['delivery_pickup_phone.required'] = __('Required for pickup');
            $messages['delivery_pickup_district.required'] = __('Required for pickup');
            $messages['delivery_pickup_day.required'] = __('Required for pickup');
            if ($request->input('delivery_pickup_district') === 'other') {
                $rules['delivery_pickup_district_other'] = 'required|string|max:255';
                $messages['delivery_pickup_district_other.required'] = __('Required for pickup');
            }
        }
        $rules['use_cashback'] = 'nullable|numeric|min:0';
        $request->validate($rules, $messages);
        $cart = $this->normalizeCart($request->session()->get('shop_cart', []));
        if (empty($cart)) {
            return redirect()->route('shop.home')->with('message', __('Cart is empty'));
        }
        $productIds = array_unique(array_column($cart, 'product_id'));
        $products = Product::whereIn('id', $productIds)->where('available_in_bot', true)->with('variants')->get()->keyBy('id');
        $orderItems = [];
        foreach ($cart as $entry) {
            $productId = (int) ($entry['product_id'] ?? 0);
            $variantId = (int) ($entry['variant_id'] ?? 0);
            $qty = (int) ($entry['quantity'] ?? 0);
            if ($qty <= 0 || !isset($products[$productId])) {
                continue;
            }
            $product = $products[$productId];
            $variant = $variantId > 0 ? $product->variants->firstWhere('id', $variantId) : null;
            if ($product->hasVariants()) {
                if (!$variant || ($variant->quantity ?? 0) < $qty) {
                    return back()->with('error', __('Insufficient product'));
                }
            } else {
                if (($product->quantity ?? 0) < $qty) {
                    return back()->with('error', __('Insufficient product'));
                }
            }
            $orderItems[] = [
                'product' => $product,
                'variant' => $variant,
                'variant_name' => $variant?->name,
                'quantity' => $qty,
            ];
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
                    $variant = $item['variant'];
                    $qty = $item['quantity'];
                    $salePrice = (float) $product->purchase_price;
                    if ($variant) {
                        ProductVariant::where('id', $variant->id)->where('product_id', $product->id)->decrement('quantity', $qty);
                    } else {
                        $product->decrement('quantity', $qty);
                    }
                    $profit = ($salePrice - ($product->purchase_price ?? 0)) * $qty;
                    $totalProfit += $profit;
                    $orderTotal += $salePrice * $qty;
                    $saleItemsData[] = [
                        'product' => $product,
                        'product_variant_id' => $variant?->id,
                        'variant_name' => $item['variant_name'],
                        'quantity' => $qty,
                        'sale_price' => $salePrice,
                        'profit' => $profit,
                    ];
                }
                $client = Client::findOrCreateByTelegram(
                    $request->input('telegram_user_id'),
                    $request->input('telegram_username')
                );
                $useCashback = 0.0;
                if ($client && $orderTotal > 0) {
                    $useCashback = (float) $request->input('use_cashback', 0);
                    $useCashback = min($useCashback, (float) $client->cashback_balance, $orderTotal);
                    $useCashback = round($useCashback, 2);
                    if ($useCashback > 0) {
                        $client->update(['cashback_balance' => (float) $client->cashback_balance - $useCashback]);
                    }
                }
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
                if (Schema::hasColumn('sales', 'delivery_paczkomat_code')) {
                    $saleData['delivery_paczkomat_code'] = $request->input('delivery_method') === 'paczkomat' ? trim((string) $request->input('delivery_paczkomat_code')) : null;
                }
                if (Schema::hasColumn('sales', 'delivery_pickup_name')) {
                    $saleData['delivery_pickup_name'] = $request->input('delivery_method') === 'osobisty_odbior' ? trim((string) $request->input('delivery_pickup_name')) : null;
                    $saleData['delivery_pickup_phone'] = $request->input('delivery_method') === 'osobisty_odbior' ? trim((string) $request->input('delivery_pickup_phone')) : null;
                    $district = $request->input('delivery_method') === 'osobisty_odbior' ? trim((string) $request->input('delivery_pickup_district')) : null;
                    if ($district === 'other' && $request->filled('delivery_pickup_district_other')) {
                        $district = trim((string) $request->input('delivery_pickup_district_other'));
                    }
                    $saleData['delivery_pickup_district'] = $district;
                    if (Schema::hasColumn('sales', 'delivery_pickup_day')) {
                        $saleData['delivery_pickup_day'] = $request->input('delivery_method') === 'osobisty_odbior' ? trim((string) $request->input('delivery_pickup_day')) : null;
                    }
                }
                $sale = Sale::create($saleData);
                foreach ($saleItemsData as $data) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $data['product']->id,
                        'product_variant_id' => $data['product_variant_id'] ?? null,
                        'variant_name' => $data['variant_name'] ?? null,
                        'quantity' => $data['quantity'],
                        'sale_price' => $data['sale_price'],
                        'profit' => $data['profit'],
                    ]);
                }
                // Кешбек не нараховується автоматично — лише коли менеджер надасть у картці клієнта
                if ($request->filled('telegram_user_id') || $request->filled('telegram_username')) {
                    session([
                        'shop_telegram_user_id' => $request->input('telegram_user_id'),
                        'shop_telegram_username' => $request->input('telegram_username'),
                    ]);
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
