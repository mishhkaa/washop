<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaleController extends Controller
{
    /** Район для списання залишків: для самовивозу — район клієнта, для Paczkomat — Урсинув. */
    private function getDeductionDistrict(Sale $sale): ?string
    {
        if (!Schema::hasColumn('sales', 'delivery_method')) {
            return null;
        }
        if (($sale->delivery_method ?? '') === 'osobisty_odbior' && $sale->delivery_pickup_district) {
            return $sale->delivery_pickup_district;
        }
        if (($sale->delivery_method ?? '') === 'paczkomat') {
            return Product::DISTRICT_URSYNOW;
        }
        return null;
    }

    /** Назва колонки кількості для району (quantity_ursynow / quantity_praga) або null для quantity. */
    private function quantityColumnForDistrict(?string $district): ?string
    {
        if (!$district || !Schema::hasColumn('products', 'quantity_ursynow')) {
            return null;
        }
        if ($district === Product::DISTRICT_URSYNOW) {
            return 'quantity_ursynow';
        }
        if ($district === Product::DISTRICT_PRAGA) {
            return 'quantity_praga';
        }
        return null;
    }

    public function index(Request $request)
    {
        $query = Sale::with(['product', 'manager', 'saleItems.product']);
        
        // Фільтр по менеджеру
        if ($request->has('manager_id') && $request->manager_id) {
            $query->where('manager_id', $request->manager_id);
        }
        
        // Фільтр по товару
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }
        
        // Фільтр по даті від
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        // Фільтр по даті до
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Фільтр по джерелу (crm | bot)
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        $sales = $query->orderBy('created_at', 'desc')->get();
        
        // Дані для фільтрів
        $managers = \App\Models\User::where('role', 'manager')->get();
        $products = \App\Models\Product::all();
        
        return view('admin.sales.index', compact('sales', 'managers', 'products'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Перевіряємо чи це комбінований продаж
        $isCombined = $request->has('is_combined') && $request->is_combined == '1';
        $saleItems = $request->input('sale_items', []);
        
        if ($isCombined && empty($saleItems)) {
            return back()->withErrors(['error' => 'Для комбінованого продажу потрібно додати хоча б один товар'])->withInput();
        }

        if (!$isCombined) {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'sale_price' => 'required|numeric|min:0',
                'manager_id' => 'nullable|exists:users,id',
                'profit_to_admin' => 'nullable|boolean',
            ]);
        } else {
            $validated = $request->validate([
                'manager_id' => 'nullable|exists:users,id',
                'profit_to_admin' => 'nullable|boolean',
            ]);
        }

        // Перевіряємо куди йде прибуток
        $profitToAdmin = isset($validated['profit_to_admin']) && $validated['profit_to_admin'] == '1';
        
        // Якщо manager_id не вказано
        if (empty($validated['manager_id'])) {
            if ($profitToAdmin) {
                // Якщо прибуток йде адміну, встановлюємо manager_id на адміна (поточного користувача)
                $validated['manager_id'] = auth()->id();
            } else {
                // Якщо прибуток йде менеджеру, шукаємо першого менеджера
                $manager = \App\Models\User::where('role', 'manager')->first();
                if ($manager) {
                    $validated['manager_id'] = $manager->id;
                } else {
                    $validated['manager_id'] = auth()->id();
                }
            }
        }

        // Перевіряємо що manager_id існує в базі
        $manager = \App\Models\User::lockForUpdate()->find($validated['manager_id']);
        if (!$manager) {
            return back()->withErrors(['manager_id' => 'Користувач не знайдений'])->withInput();
        }

        // Використовуємо транзакцію для безпечного оновлення
        try {
            DB::transaction(function () use ($validated, $isCombined, $saleItems, $profitToAdmin, $manager) {
                $totalProfit = 0;
                $totalRevenue = 0; // сума продажу (ціна продажу × кількість) — йде на баланс менеджера
                
                if ($isCombined) {
                    // Обробка комбінованого продажу
                    foreach ($saleItems as $item) {
                        $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                        $quantity = (int)$item['quantity'];
                        $salePrice = (float)$item['sale_price'];
                        
                        // Перевіряємо достатність кількості на складі
                        $availableQuantity = $product->quantity ?? 0;
                        if ($availableQuantity < $quantity) {
                            throw new \Exception('Недостатньо товару на складі для ' . $product->name . '. Доступно: ' . $availableQuantity . ' шт.');
                        }
                        
                        $totalRevenue += $salePrice * $quantity;
                        $productPrice = $product->purchase_price ?? 0;
                        $totalProfit += ($salePrice - $productPrice) * $quantity;
                        $product->decrement('quantity', $quantity);
                    }
                    
                    $sale = Sale::create([
                        'manager_id' => $validated['manager_id'],
                        'is_combined' => true,
                        'profit_to_admin' => $profitToAdmin,
                        'profit' => $totalProfit,
                        'sale_price' => 0,
                        'quantity' => 0,
                        'status' => 'accepted',
                    ]);
                    
                    foreach ($saleItems as $item) {
                        $product = Product::findOrFail($item['product_id']);
                        $quantity = (int)$item['quantity'];
                        $salePrice = (float)$item['sale_price'];
                        $productPrice = $product->purchase_price ?? 0;
                        $profit = ($salePrice - $productPrice) * $quantity;
                        
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $quantity,
                            'sale_price' => $salePrice,
                            'profit' => $profit,
                        ]);
                    }
                } else {
                    $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
                    $availableQuantity = $product->quantity ?? 0;
                    if ($availableQuantity < $validated['quantity']) {
                        throw new \Exception('Недостатньо товару на складі. Доступно: ' . $availableQuantity . ' шт.');
                    }
                    
                    $salePrice = $validated['sale_price'];
                    $quantity = $validated['quantity'];
                    $productPrice = $product->purchase_price ?? 0;
                    $validated['profit'] = ($salePrice - $productPrice) * $quantity;
                    $validated['is_combined'] = false;
                    $validated['profit_to_admin'] = $profitToAdmin;
                    $totalProfit = $validated['profit'];
                    $totalRevenue = $salePrice * $quantity;
                    $validated['status'] = 'accepted';

                    Sale::create($validated);
                    $product->decrement('quantity', $quantity);
                }
                
                // На баланс менеджера йде сума продажу (ціна продажу × кількість), а не прибуток
                if (!$profitToAdmin && $manager->role === 'manager') {
                    $manager->increment('balance', $totalRevenue);
                }
            });
            
            return redirect()->route('admin.sales.index')
                ->with('success', 'Продаж успішно створений');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['saleItems.product', 'manager', 'client']);
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Зміна статусу замовлення. При «Прийнято» — списання залишків зі складу.
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate(['status' => 'required|in:pending,accepted,cancelled,completed']);
        $newStatus = $request->input('status');
        $previousStatus = $sale->status ?? 'accepted';

        if ($newStatus === 'completed') {
            $sale->update(['status' => 'completed']);
            return back()->with('success', 'Замовлення позначено як виконане.');
        }

        if ($newStatus === 'accepted' && $previousStatus !== 'accepted') {
            $district = $this->getDeductionDistrict($sale);
            DB::transaction(function () use ($sale, $district) {
                $sale->load('saleItems.product');
                foreach ($sale->saleItems as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        continue;
                    }
                    $qty = (int) $item->quantity;
                    $col = $this->quantityColumnForDistrict($district);
                    if ($item->product_variant_id) {
                        if ($col) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->decrement($col, $qty);
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->decrement('quantity', $qty);
                        } else {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->decrement('quantity', $qty);
                        }
                    } else {
                        if ($col) {
                            $product->decrement($col, $qty);
                            $product->decrement('quantity', $qty);
                        } else {
                            $product->decrement('quantity', $qty);
                        }
                    }
                }
                $sale->update([
                    'status' => 'accepted',
                    'manager_id' => auth()->id(),
                ]);
            });
            return back()->with('success', 'Замовлення прийнято. Залишки списано зі складу.');
        }

        if ($newStatus === 'cancelled' && $previousStatus === 'accepted') {
            $district = $this->getDeductionDistrict($sale);
            DB::transaction(function () use ($sale, $district) {
                $sale->load('saleItems.product');
                foreach ($sale->saleItems as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        continue;
                    }
                    $qty = (int) $item->quantity;
                    $col = $this->quantityColumnForDistrict($district);
                    if ($item->product_variant_id) {
                        if ($col) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->increment($col, $qty);
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->increment('quantity', $qty);
                        } else {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->where('product_id', $product->id)
                                ->increment('quantity', $qty);
                        }
                    } else {
                        if ($col) {
                            $product->increment($col, $qty);
                            $product->increment('quantity', $qty);
                        } else {
                            $product->increment('quantity', $qty);
                        }
                    }
                }
                $sale->update(['status' => 'cancelled']);
            });
            return back()->with('success', 'Замовлення скасовано. Залишки повернено на склад.');
        }

        $sale->update(['status' => $newStatus]);
        return back()->with('success', 'Статус оновлено.');
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $sale = Sale::with('saleItems.product')->findOrFail($id);
                $wasAccepted = in_array($sale->status ?? 'accepted', ['accepted', 'completed'], true);

                // Повертаємо залишки тільки якщо замовлення було в статусі «Прийнято» (залишки вже списані)
                if ($wasAccepted) {
                    $district = $this->getDeductionDistrict($sale);
                    $col = $this->quantityColumnForDistrict($district);
                    if ($sale->is_combined) {
                        foreach ($sale->saleItems as $item) {
                            $product = Product::lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $qty = (int) $item->quantity;
                                if ($item->product_variant_id) {
                                    if ($col) {
                                        ProductVariant::where('id', $item->product_variant_id)
                                            ->where('product_id', $product->id)
                                            ->increment($col, $qty);
                                        ProductVariant::where('id', $item->product_variant_id)
                                            ->where('product_id', $product->id)
                                            ->increment('quantity', $qty);
                                    } else {
                                        ProductVariant::where('id', $item->product_variant_id)
                                            ->where('product_id', $product->id)
                                            ->increment('quantity', $qty);
                                    }
                                } else {
                                    if ($col) {
                                        $product->increment($col, $qty);
                                        $product->increment('quantity', $qty);
                                    } else {
                                        $product->increment('quantity', $qty);
                                    }
                                }
                            }
                        }
                    } else {
                        if ($sale->product_id) {
                            $product = Product::lockForUpdate()->find($sale->product_id);
                            if ($product) {
                                $qty = (int) $sale->quantity;
                                if ($col) {
                                    $product->increment($col, $qty);
                                } else {
                                    $product->increment('quantity', $qty);
                                }
                            }
                        }
                    }
                }

                // Повертаємо з балансу суму продажу (ціна продажу × кількість), яку нараховували
                if ($wasAccepted && !$sale->profit_to_admin && $sale->manager_id) {
                    $manager = \App\Models\User::lockForUpdate()->find($sale->manager_id);
                    if ($manager && $manager->role === 'manager') {
                        $amountToReturn = $sale->is_combined
                            ? $sale->saleItems->sum(fn ($item) => ($item->sale_price ?? 0) * ($item->quantity ?? 0))
                            : ($sale->sale_price ?? 0) * ($sale->quantity ?? 0);
                        if ($amountToReturn > 0 && $manager->balance >= $amountToReturn) {
                            $manager->decrement('balance', $amountToReturn);
                        }
                    }
                }

                $sale->delete();
            });

            return redirect()->route('admin.sales.index')
                ->with('success', 'Продаж успішно видалено');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Помилка при видаленні: ' . $e->getMessage()]);
        }
    }
}
