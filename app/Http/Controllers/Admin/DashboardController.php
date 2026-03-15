<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private function acceptedSalesQuery()
    {
        $q = Sale::query();
        if (Schema::hasColumn('sales', 'status')) {
            $q->whereIn('status', ['accepted', 'completed']);
        }
        return $q;
    }

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

    public function index()
    {
        $stats = [
            'products_count' => Product::count(),
            'users_count' => User::where('role', '!=', 'admin')->count(),
            'managers_count' => User::where('role', 'manager')->count(),
            'sales_count' => Sale::count(),
        ];

        $accepted = $this->acceptedSalesQuery();
        $acceptedIds = (clone $accepted)->pluck('id');

        $revenue = $this->revenueForSaleIds($acceptedIds);
        $totalProfit = (float) (clone $accepted)->selectRaw('SUM(profit) as total')->value('total') ?? 0;

        $countAccepted = $acceptedIds->count();
        $avgSaleAmount = $countAccepted > 0 ? $revenue / $countAccepted : 0;

        $salesToday = Sale::whereDate('created_at', today())->count();
        $salesThisWeek = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $salesThisMonth = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $topProducts = collect();
        if ($acceptedIds->isNotEmpty()) {
            $ids = $acceptedIds->toArray();
            $byProduct = SaleItem::whereIn('sale_id', $ids)
                ->selectRaw('product_id, SUM(quantity) as qty_sold')
                ->groupBy('product_id')
                ->orderByDesc('qty_sold')
                ->limit(5)
                ->get();
            $productIds = $byProduct->pluck('product_id')->filter()->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $topProducts = $byProduct->map(function ($row) use ($products) {
                $p = $products->get($row->product_id);
                return (object) [
                    'id' => $row->product_id,
                    'name' => $p ? $p->name : '—',
                    'sales_count' => (int) $row->qty_sold,
                ];
            });
        }

        $topManagers = User::where('role', 'manager')
            ->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get()
            ->filter(fn ($m) => $m->sales_count > 0);

        $recentSales = Sale::with(['product', 'manager', 'saleItems.product'])
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
