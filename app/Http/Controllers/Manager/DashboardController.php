<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Кількість товарів, призначених менеджеру або доступних всім
        $myProductsCount = \App\Models\Product::where(function($query) {
            $query->where('manager_id', auth()->id())
                  ->orWhereNull('manager_id');
        })->count();

        // Всього продажів менеджера
        $salesQuery = \App\Models\Sale::where('manager_id', auth()->id());
        
        // Фільтр по періоду
        if ($request->has('period_from') && $request->period_from) {
            $salesQuery->whereDate('created_at', '>=', $request->period_from);
        }
        
        if ($request->has('period_to') && $request->period_to) {
            $salesQuery->whereDate('created_at', '<=', $request->period_to);
        }
        
        $totalSales = $salesQuery->count();

        // Загальний дохід (сума всіх продажів, включаючи комбіновані)
        $sales = \App\Models\Sale::where('manager_id', auth()->id())
            ->with('saleItems')
            ->get();
            
        // Фільтр по періоду для продажів
        if ($request->has('period_from') && $request->period_from) {
            $sales = $sales->filter(function($sale) use ($request) {
                return $sale->created_at->format('Y-m-d') >= $request->period_from;
            });
        }
        
        if ($request->has('period_to') && $request->period_to) {
            $sales = $sales->filter(function($sale) use ($request) {
                return $sale->created_at->format('Y-m-d') <= $request->period_to;
            });
        }
        
        $totalRevenue = 0;
        foreach ($sales as $sale) {
            if ($sale->is_combined) {
                // Для комбінованого продажу
                foreach ($sale->saleItems as $item) {
                    $totalRevenue += ($item->sale_price ?? 0) * ($item->quantity ?? 0);
                }
            } else {
                // Для звичайного продажу
                $totalRevenue += ($sale->sale_price ?? 0) * ($sale->quantity ?? 0);
            }
        }

        // Останні 5 продажів
        $recentSales = \App\Models\Sale::where('manager_id', auth()->id())
            ->with(['product', 'saleItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Поточний баланс
        $currentBalance = auth()->user()->balance ?? 0;

        return view('manager.dashboard', compact(
            'myProductsCount',
            'totalSales',
            'totalRevenue',
            'recentSales',
            'currentBalance'
        ));
    }
}
