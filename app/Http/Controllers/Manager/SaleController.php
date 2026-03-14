<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::where('manager_id', auth()->id())
            ->with(['product', 'saleItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.sales.index', compact('sales'));
    }

    public function create()
    {
        // Менеджер бачить тільки свої товари або товари без призначення (для всіх)
        $products = Product::where(function($query) {
            $query->where('manager_id', auth()->id())
                  ->orWhereNull('manager_id');
        })->get();

        return view('manager.sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        // Перевіряємо що користувач існує
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Необхідно увійти в систему']);
        }

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
            ]);
        } else {
            $validated = [];
        }

        $validated['manager_id'] = $user->id;

        // Використовуємо транзакцію для безпечного оновлення
        try {
            DB::transaction(function () use ($validated, $isCombined, $saleItems, $user) {
                $totalProfit = 0;
                $totalRevenue = 0; // сума продажу (ціна продажу × кількість) — йде на баланс
                
                if ($isCombined) {
                    foreach ($saleItems as $item) {
                        $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                        if ($product->manager_id !== null && $product->manager_id !== $user->id) {
                            throw new \Exception('Товар ' . $product->name . ' не доступний для вас');
                        }
                        $quantity = (int)$item['quantity'];
                        $salePrice = (float)$item['sale_price'];
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
                        'manager_id' => $user->id,
                        'is_combined' => true,
                        'profit_to_admin' => false,
                        'profit' => $totalProfit,
                        'sale_price' => 0,
                        'quantity' => 0,
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
                    if ($product->manager_id !== null && $product->manager_id !== $user->id) {
                        throw new \Exception('Цей товар не доступний для вас');
                    }
                    $availableQuantity = $product->quantity ?? 0;
                    if ($availableQuantity < $validated['quantity']) {
                        throw new \Exception('Недостатньо товару на складі. Доступно: ' . $availableQuantity . ' шт.');
                    }
                    $salePrice = $validated['sale_price'];
                    $quantity = $validated['quantity'];
                    $productPrice = $product->purchase_price ?? 0;
                    $validated['profit'] = ($salePrice - $productPrice) * $quantity;
                    $validated['is_combined'] = false;
                    $validated['profit_to_admin'] = false;
                    $totalProfit = $validated['profit'];
                    $totalRevenue = $salePrice * $quantity;
                    
                    Sale::create($validated);
                    $product->decrement('quantity', $quantity);
                }
                
                // На баланс йде сума продажу (ціна продажу × кількість), а не прибуток
                $user->lockForUpdate()->increment('balance', $totalRevenue);
            });
            
            return redirect()->route('manager.sales.index')
                ->with('success', 'Продаж успішно створений');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $sale = Sale::with('saleItems.product')->findOrFail($id);
                
                // Перевіряємо, що менеджер може видаляти тільки свої продажі
                if ($sale->manager_id !== auth()->id()) {
                    throw new \Exception('Ви не маєте права видаляти цей продаж');
                }
                
                // Якщо це комбінований продаж, обробляємо всі sale_items
                if ($sale->is_combined) {
                    foreach ($sale->saleItems as $item) {
                        $product = Product::lockForUpdate()->findOrFail($item->product_id);
                        // Повертаємо кількість товару назад на склад
                        $product->increment('quantity', $item->quantity);
                    }
                } else {
                    // Для звичайного продажу
                    if ($sale->product_id) {
                        $product = Product::lockForUpdate()->findOrFail($sale->product_id);
                        // Повертаємо кількість товару назад на склад
                        $product->increment('quantity', $sale->quantity);
                    }
                }
                
                // Повертаємо з балансу суму продажу (ціна продажу × кількість)
                if (!$sale->profit_to_admin && $sale->manager_id) {
                    $manager = \App\Models\User::lockForUpdate()->find($sale->manager_id);
                    if ($manager) {
                        $amountToReturn = $sale->is_combined
                            ? $sale->saleItems->sum(fn ($item) => ($item->sale_price ?? 0) * ($item->quantity ?? 0))
                            : ($sale->sale_price ?? 0) * ($sale->quantity ?? 0);
                        if ($amountToReturn > 0 && $manager->balance >= $amountToReturn) {
                            $manager->decrement('balance', $amountToReturn);
                        }
                    }
                }
                
                // Видаляємо продаж (cascade видалить sale_items автоматично)
                $sale->delete();
            });
            
            return redirect()->route('manager.sales.index')
                ->with('success', 'Продаж успішно видалено');
                
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Помилка при видаленні: ' . $e->getMessage()]);
        }
    }
}
