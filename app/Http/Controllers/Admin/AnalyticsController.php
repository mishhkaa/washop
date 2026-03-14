<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Загальний дохід (revenue) - сума всіх продажів
        $totalRevenue = Sale::selectRaw('SUM(quantity * sale_price) as total')
            ->first()
            ->total ?? 0;

        // Загальний прибуток (profit) - дохід мінус собівартість
        $totalProfit = Sale::selectRaw('SUM(profit) as total_profit')
            ->first()
            ->total_profit ?? 0;

        // Маржинальність (%)
        $marginality = ($totalRevenue > 0) ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Середній чек
        $avgSaleAmount = Sale::selectRaw('AVG(quantity * sale_price) as avg')
            ->first()
            ->avg ?? 0;

        // Середній прибуток на продаж
        $avgProfitPerSale = Sale::selectRaw('AVG(profit) as avg')
            ->first()
            ->avg ?? 0;

        // Кількість продажів
        $totalSales = Sale::count();

        // Загальна кількість проданих товарів
        $totalQuantitySold = Sale::selectRaw('SUM(quantity) as total')
            ->first()
            ->total ?? 0;

        // ============================================
        // Продажі за періоди
        // ============================================
        $salesToday = Sale::whereDate('created_at', today())
            ->selectRaw('COUNT(*) as count, SUM(quantity * sale_price) as revenue, SUM(profit) as profit')
            ->first();

        $salesThisWeek = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('COUNT(*) as count, SUM(quantity * sale_price) as revenue, SUM(profit) as profit')
            ->first();

        $salesThisMonth = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('COUNT(*) as count, SUM(quantity * sale_price) as revenue, SUM(profit) as profit')
            ->first();

        $salesThisYear = Sale::whereYear('created_at', now()->year)
            ->selectRaw('COUNT(*) as count, SUM(quantity * sale_price) as revenue, SUM(profit) as profit')
            ->first();

        // Продажі за останні 30 днів (для графіка) - SQLite сумісність
        $salesByDay = Sale::selectRaw('date(created_at) as date, 
                COUNT(*) as count, 
                SUM(quantity * sale_price) as revenue, 
                SUM(profit) as profit')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ============================================
        // Топ товари за доходом
        // ============================================
        $topProductsByRevenue = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.id, products.name,
                SUM(sales.quantity * sales.sale_price) as revenue,
                SUM(sales.profit) as profit,
                SUM(sales.quantity) as quantity_sold,
                COUNT(sales.id) as sales_count')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Топ товари за прибутком
        $topProductsByProfit = DB::table('sales')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->selectRaw('products.id, products.name,
                SUM(sales.quantity * sales.sale_price) as revenue,
                SUM(sales.profit) as profit,
                SUM(sales.quantity) as quantity_sold,
                COUNT(sales.id) as sales_count')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('profit')
            ->limit(10)
            ->get();

        // ============================================
        // Топ менеджери
        // ============================================
        $topManagers = DB::table('sales')
            ->join('users', 'sales.manager_id', '=', 'users.id')
            ->where('users.role', 'manager')
            ->selectRaw('users.id, users.name,
                COUNT(sales.id) as sales_count,
                SUM(sales.quantity * sales.sale_price) as revenue,
                SUM(sales.profit) as profit')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(function ($manager) {
                $manager->avg_revenue = $manager->sales_count > 0 
                    ? $manager->revenue / $manager->sales_count 
                    : 0;
                return $manager;
            });

        // ============================================
        // Статистика по місяцях (останні 12 місяців) - SQLite сумісність
        // ============================================
        $monthlyStats = Sale::selectRaw('
                strftime("%Y-%m", created_at) as month,
                COUNT(*) as sales_count,
                SUM(quantity * sale_price) as revenue,
                SUM(profit) as profit,
                SUM(quantity) as quantity_sold
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ============================================
        // Найприбутковіші дні тижня - SQLite сумісність
        // ============================================
        $weekdayStats = Sale::selectRaw('
                CASE strftime("%w", created_at)
                    WHEN "0" THEN "Неділя"
                    WHEN "1" THEN "Понеділок"
                    WHEN "2" THEN "Вівторок"
                    WHEN "3" THEN "Середа"
                    WHEN "4" THEN "Четвер"
                    WHEN "5" THEN "П\'ятниця"
                    WHEN "6" THEN "Субота"
                END as weekday,
                CAST(strftime("%w", created_at) AS INTEGER) as day_num,
                COUNT(*) as sales_count,
                SUM(quantity * sale_price) as revenue,
                SUM(profit) as profit
            ')
            ->groupBy('weekday', 'day_num')
            ->orderBy('day_num')
            ->get();

        return view('admin.analytics.index', compact(
            'totalRevenue',
            'totalProfit',
            'marginality',
            'avgSaleAmount',
            'avgProfitPerSale',
            'totalSales',
            'totalQuantitySold',
            'salesToday',
            'salesThisWeek',
            'salesThisMonth',
            'salesThisYear',
            'salesByDay',
            'topProductsByRevenue',
            'topProductsByProfit',
            'topManagers',
            'monthlyStats',
            'weekdayStats'
        ));
    }
}
