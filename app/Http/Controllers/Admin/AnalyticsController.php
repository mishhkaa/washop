<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /** Запит прийнятих та виконаних продажів (в аналітиці рахуємо їх). */
    private function acceptedQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = Sale::query();
        if (Schema::hasColumn('sales', 'status')) {
            $q->whereIn('status', ['accepted', 'completed']);
        }
        return $q;
    }

    /** Дохід по списку sale_id: з sale_items + з sales (прості). */
    private function revenueForSaleIds($saleIds): float
    {
        if ($saleIds->isEmpty()) {
            return 0.0;
        }
        $ids = $saleIds->toArray();
        $fromItems = (float) SaleItem::whereIn('sale_id', $ids)->selectRaw('SUM(quantity * sale_price) as t')->value('t');
        $fromSales = (float) Sale::whereIn('id', $ids)->where('is_combined', false)->selectRaw('SUM(quantity * sale_price) as t')->value('t');
        return $fromItems + $fromSales;
    }

    /** Агрегат за період: count, revenue, profit (лише прийняті). */
    private function periodStats($query): object
    {
        $ids = $query->pluck('id');
        $count = $ids->count();
        $revenue = $this->revenueForSaleIds($ids);
        $profit = (float) Sale::whereIn('id', $ids->isEmpty() ? [-1] : $ids)->selectRaw('SUM(profit) as t')->value('t');
        return (object) ['count' => $count, 'revenue' => $revenue, 'profit' => $profit];
    }

    /** Продажі по днях за останні 30 днів (для графіка). */
    private function salesByDayStats($query): \Illuminate\Support\Collection
    {
        $byDate = $query->get()->groupBy(fn ($s) => $s->created_at->format('Y-m-d'));
        return $byDate->map(function ($sales, $date) {
            $ids = $sales->pluck('id');
            return (object) [
                'date' => $date,
                'count' => $ids->count(),
                'revenue' => $this->revenueForSaleIds($ids),
                'profit' => $sales->sum('profit'),
            ];
        })->sortBy('date')->values();
    }

    /** Топ товари за доходом (з sale_items + sales, лише прийняті). */
    private function topProductsByRevenue(): \Illuminate\Support\Collection
    {
        $accepted = $this->acceptedQuery();
        $ids = (clone $accepted)->pluck('id');
        if ($ids->isEmpty()) {
            return collect();
        }
        $fromItems = SaleItem::whereIn('sale_id', $ids)
            ->selectRaw('product_id, SUM(quantity * sale_price) as revenue, SUM(profit) as profit, SUM(quantity) as quantity_sold')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        $fromSales = (clone $accepted)->where('is_combined', false)->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(quantity * sale_price) as revenue, SUM(profit) as profit, SUM(quantity) as quantity_sold')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        $productIds = $fromItems->keys()->merge($fromSales->keys())->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        return $productIds->map(function ($pid) use ($fromItems, $fromSales, $products) {
            $a = $fromItems->get($pid);
            $b = $fromSales->get($pid);
            $revenue = ($a ? (float) $a->revenue : 0) + ($b ? (float) $b->revenue : 0);
            $profit = ($a ? (float) $a->profit : 0) + ($b ? (float) $b->profit : 0);
            $qty = ($a ? (int) $a->quantity_sold : 0) + ($b ? (int) $b->quantity_sold : 0);
            return (object) [
                'id' => $pid,
                'name' => $products->get($pid)?->name ?? '—',
                'revenue' => $revenue,
                'profit' => $profit,
                'quantity_sold' => $qty,
                'sales_count' => 0,
            ];
        })->sortByDesc('revenue')->take(10)->values();
    }

    /** Топ товари за прибутком (та сама агрегація, сортування по прибутку). */
    private function topProductsByProfit(): \Illuminate\Support\Collection
    {
        $accepted = $this->acceptedQuery();
        $ids = (clone $accepted)->pluck('id');
        if ($ids->isEmpty()) {
            return collect();
        }
        $fromItems = SaleItem::whereIn('sale_id', $ids)
            ->selectRaw('product_id, SUM(quantity * sale_price) as revenue, SUM(profit) as profit, SUM(quantity) as quantity_sold')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        $fromSales = (clone $accepted)->where('is_combined', false)->whereNotNull('product_id')
            ->selectRaw('product_id, SUM(quantity * sale_price) as revenue, SUM(profit) as profit, SUM(quantity) as quantity_sold')
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');
        $productIds = $fromItems->keys()->merge($fromSales->keys())->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        return $productIds->map(function ($pid) use ($fromItems, $fromSales, $products) {
            $a = $fromItems->get($pid);
            $b = $fromSales->get($pid);
            $revenue = ($a ? (float) $a->revenue : 0) + ($b ? (float) $b->revenue : 0);
            $profit = ($a ? (float) $a->profit : 0) + ($b ? (float) $b->profit : 0);
            $qty = ($a ? (int) $a->quantity_sold : 0) + ($b ? (int) $b->quantity_sold : 0);
            return (object) [
                'id' => $pid,
                'name' => $products->get($pid)?->name ?? '—',
                'revenue' => $revenue,
                'profit' => $profit,
                'quantity_sold' => $qty,
                'sales_count' => 0,
            ];
        })->sortByDesc('profit')->take(10)->values();
    }

    /** Статистика по місяцях (об'єкти з month, sales_count, revenue, profit, quantity_sold). */
    private function monthlyStats($query): \Illuminate\Support\Collection
    {
        $byMonth = $query->get()->groupBy(fn ($s) => $s->created_at->format('Y-m'));
        return $byMonth->map(function ($sales, $month) {
            $ids = $sales->pluck('id');
            return (object) [
                'month' => $month,
                'sales_count' => $ids->count(),
                'revenue' => $this->revenueForSaleIds($ids),
                'profit' => $sales->sum('profit'),
                'quantity_sold' => 0,
            ];
        })->sortBy('month')->values();
    }

    public function index()
    {
        $accepted = $this->acceptedQuery();

        // Загальний дохід (лише прийняті; комбіновані — з sale_items)
        $acceptedIds = (clone $accepted)->pluck('id');
        $totalRevenue = $this->revenueForSaleIds($acceptedIds);

        // Загальний прибуток (лише прийняті)
        $totalProfit = (float) (clone $accepted)->selectRaw('SUM(profit) as total_profit')->value('total_profit');

        // Маржинальність (%)
        $marginality = ($totalRevenue > 0) ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Кількість прийнятих продажів
        $totalSales = (clone $accepted)->count();

        // Середній чек
        $avgSaleAmount = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Середній прибуток на продаж
        $avgProfitPerSale = $totalSales > 0 ? $totalProfit / $totalSales : 0;

        // Загальна кількість проданих товарів
        $qtyFromItems = $acceptedIds->isEmpty() ? 0 : (float) SaleItem::whereIn('sale_id', $acceptedIds)->selectRaw('SUM(quantity) as t')->value('t');
        $qtyFromSales = (float) (clone $accepted)->where('is_combined', false)->selectRaw('SUM(quantity) as t')->value('t');
        $totalQuantitySold = $qtyFromItems + $qtyFromSales;

        // ============================================
        // Продажі за періоди (лише прийняті)
        // ============================================
        $salesToday = $this->periodStats((clone $accepted)->whereDate('created_at', today()));
        $salesThisWeek = $this->periodStats((clone $accepted)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]));
        $salesThisMonth = $this->periodStats((clone $accepted)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year));
        $salesThisYear = $this->periodStats((clone $accepted)->whereYear('created_at', now()->year));

        // Продажі за останні 30 днів (для графіка)
        $salesByDay = $this->salesByDayStats((clone $accepted)->where('created_at', '>=', now()->subDays(30)));

        // Топ товари за доходом та прибутком (лише прийняті; з sale_items + sales)
        $topProductsByRevenue = $this->topProductsByRevenue();
        $topProductsByProfit = $this->topProductsByProfit();

        // Топ менеджери (лише прийняті)
        $managersQuery = (clone $accepted)->selectRaw('manager_id, COUNT(*) as sales_count, SUM(profit) as profit')->groupBy('manager_id');
        $managerRows = $managersQuery->get();
        $topManagers = User::where('role', 'manager')->whereIn('id', $managerRows->pluck('manager_id'))->get()->map(function ($user) use ($managerRows, $accepted) {
            $row = $managerRows->firstWhere('manager_id', $user->id);
            $ids = (clone $accepted)->where('manager_id', $user->id)->pluck('id');
            $revenue = $this->revenueForSaleIds($ids);
            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'sales_count' => $row ? $row->sales_count : 0,
                'revenue' => $revenue,
                'profit' => $row ? (float) $row->profit : 0,
                'avg_revenue' => $row && $row->sales_count > 0 ? $revenue / $row->sales_count : 0,
            ];
        })->sortByDesc('revenue')->take(10)->values();

        // Статистика по місяцях (лише прийняті)
        $monthlyStats = $this->monthlyStats((clone $accepted)->where('created_at', '>=', now()->subMonths(12)));

        // Дні тижня (лише прийняті)
        $dayNames = ['0' => 'Неділя', '1' => 'Понеділок', '2' => 'Вівторок', '3' => 'Середа', '4' => 'Четвер', '5' => 'П\'ятниця', '6' => 'Субота'];
        $weekdayStats = (clone $accepted)
            ->selectRaw('strftime("%w", created_at) as day_num, COUNT(*) as sales_count, SUM(profit) as profit')
            ->groupBy('day_num')
            ->orderBy('day_num')
            ->get()
            ->map(function ($row) use ($accepted, $dayNames) {
                $ids = (clone $accepted)->whereRaw('strftime("%w", created_at) = ?', [$row->day_num])->pluck('id');
                return (object) [
                    'weekday' => $dayNames[$row->day_num] ?? $row->day_num,
                    'day_num' => (int) $row->day_num,
                    'sales_count' => $row->sales_count,
                    'revenue' => $this->revenueForSaleIds($ids),
                    'profit' => (float) $row->profit,
                ];
            });

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
