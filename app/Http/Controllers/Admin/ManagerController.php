<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::where('role', 'manager')->get();
        return view('admin.managers.index', compact('managers'));
    }

    public function show(User $manager)
    {
        // Перевірка що це менеджер
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        // Товари призначені цьому менеджеру
        $products = Product::where('manager_id', $manager->id)
            ->with('manager')
            ->get();
        
        // Статистика
        $productsCount = $products->count();
        $totalQuantity = $products->sum('quantity');
        $totalValue = $products->sum(function($product) {
            return ($product->purchase_price ?? 0) * ($product->quantity ?? 0);
        });
        
        return view('admin.managers.show', compact('manager', 'products', 'productsCount', 'totalQuantity', 'totalValue'));
    }

    public function calculation(Request $request, User $manager)
    {
        // Перевірка що це менеджер
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        // Розраховуємо статистику для менеджера
        $query = \App\Models\Sale::where('manager_id', $manager->id);
        
        // Фільтр по періоду, якщо вказано
        if ($request->has('period_from') && $request->period_from) {
            $query->whereDate('created_at', '>=', $request->period_from);
        }
        
        if ($request->has('period_to') && $request->period_to) {
            $query->whereDate('created_at', '<=', $request->period_to);
        }
        
        $sales = $query->with('saleItems')->get();
        
        // Загальний дохід (сума всіх продажів)
        $totalRevenue = 0;
        $totalProfit = 0;
        $managerProfit = 0;
        
        foreach ($sales as $sale) {
            if ($sale->is_combined) {
                // Для комбінованого продажу
                foreach ($sale->saleItems as $item) {
                    $itemRevenue = $item->sale_price * $item->quantity;
                    $totalRevenue += $itemRevenue;
                    $totalProfit += $item->profit ?? 0;
                    
                    // Якщо прибуток йде менеджеру
                    if (!$sale->profit_to_admin) {
                        $managerProfit += $item->profit ?? 0;
                    }
                }
            } else {
                // Для звичайного продажу
                $saleRevenue = ($sale->sale_price ?? 0) * ($sale->quantity ?? 0);
                $totalRevenue += $saleRevenue;
                $totalProfit += $sale->profit ?? 0;
                
                // Якщо прибуток йде менеджеру
                if (!$sale->profit_to_admin) {
                    $managerProfit += $sale->profit ?? 0;
                }
            }
        }
        
        // Чистий прибуток менеджера (тільки продажі де profit_to_admin = false)
        $managerNetProfit = $managerProfit;
        
        // Поточний баланс менеджера
        $currentBalance = $manager->balance ?? 0;
        
        return view('admin.managers.calculation', compact(
            'manager', 
            'totalRevenue', 
            'totalProfit', 
            'managerProfit', 
            'managerNetProfit',
            'currentBalance'
        ));
    }

    public function create()
    {
        return view('admin.managers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'manager';

        User::create($validated);

        return redirect()->route('admin.managers.index')
            ->with('success', 'Менеджер успішно створений');
    }

    public function edit(User $manager)
    {
        return view('admin.managers.edit', compact('manager'));
    }

    public function update(Request $request, User $manager)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $manager->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $manager->update($validated);

        return redirect()->route('admin.managers.index')
            ->with('success', 'Менеджер успішно оновлений');
    }

    public function destroy(User $manager)
    {
        // Захист: перевіряємо що це дійсно менеджер
        if ($manager->role !== 'manager') {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Це не менеджер');
        }

        // Захист: не дозволяємо видаляти самого себе
        if ($manager->id === auth()->id()) {
            return redirect()->route('admin.managers.index')
                ->with('error', 'Не можна видалити самого себе');
        }

        $manager->delete();

        return redirect()->route('admin.managers.index')
            ->with('success', 'Менеджер успішно видалений');
    }
}
