<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Основна статистика
        $stats = [
            'products_count' => Product::count(),
            'users_count' => User::where('role', '!=', 'admin')->count(),
            'managers_count' => User::where('role', 'manager')->count(),
            'sales_count' => Sale::count(),
        ];

        // Фінансова аналітика
        $revenue = Sale::selectRaw('SUM(quantity * sale_price) as total')
            ->first()
            ->total ?? 0;

        $totalProfit = Sale::selectRaw('SUM(profit) as total')
            ->first()
            ->total ?? 0;

        $avgSaleAmount = Sale::selectRaw('AVG(quantity * sale_price) as avg')
            ->first()
            ->avg ?? 0;

        // Продажі за останні дні
        $salesToday = Sale::whereDate('created_at', today())->count();
        $salesThisWeek = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $salesThisMonth = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Топ товари
        $topProducts = Product::withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->filter(function ($product) {
                return $product->sales_count > 0;
            });

        // Топ менеджери
        $topManagers = User::where('role', 'manager')
            ->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->filter(function ($manager) {
                return $manager->sales_count > 0;
            });


        // Останні продажі
        $recentSales = Sale::with(['product', 'manager'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'revenue',
            'totalProfit',
            'avgSaleAmount',
            'salesToday',
            'salesThisWeek',
            'salesThisMonth',
            'topProducts',
            'topManagers',
            'recentSales'
        ));
    }
}
