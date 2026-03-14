@extends('layouts.app')

@section('title', 'Аналітика')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div>
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Аналітика</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Детальна аналітика продажів та фінансів</p>
    </div>

    <!-- Основні метрики -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Загальний дохід -->
        <div class="card bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-green-700 uppercase tracking-wide">Загальний дохід</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-green-800">{{ number_format($totalRevenue, 2) }} zł</p>
                    <p class="mt-1 text-xs text-green-600">Сума всіх продажів</p>
                </div>
                <div class="p-3 bg-green-200 rounded-xl">
                    <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Загальний прибуток -->
        <div class="card bg-gradient-to-br {{ $totalProfit >= 0 ? 'from-emerald-50 to-emerald-100' : 'from-red-50 to-red-100' }} border-2 {{ $totalProfit >= 0 ? 'border-emerald-200' : 'border-red-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }} uppercase tracking-wide">Загальний прибуток</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }}">{{ number_format($totalProfit, 2) }} zł</p>
                    <p class="mt-1 text-xs {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Дохід мінус собівартість</p>
                </div>
                <div class="p-3 {{ $totalProfit >= 0 ? 'bg-emerald-200' : 'bg-red-200' }} rounded-xl">
                    <svg class="w-8 h-8 {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Маржинальність -->
        <div class="card bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-blue-700 uppercase tracking-wide">Маржинальність</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-blue-800">{{ number_format($marginality, 2) }}%</p>
                    <p class="mt-1 text-xs text-blue-600">Прибуток / Дохід × 100</p>
                </div>
                <div class="p-3 bg-blue-200 rounded-xl">
                    <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Середній чек -->
        <div class="card bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-purple-700 uppercase tracking-wide">Середній чек</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-purple-800">{{ number_format($avgSaleAmount, 2) }} zł</p>
                    <p class="mt-1 text-xs text-purple-600">Середня сума продажу</p>
                </div>
                <div class="p-3 bg-purple-200 rounded-xl">
                    <svg class="w-8 h-8 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Графіки -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 sm:mb-8">
        <!-- Графік продажів за останні 30 днів -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Продажі за останні 30 днів</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Графік по місяцях -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Дохід та прибуток по місяцях</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Графік топ товарів за доходом -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Топ товари за доходом</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="productsRevenueChart"></canvas>
            </div>
        </div>

        <!-- Графік по днях тижня -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Дохід по днях тижня</h2>
            <div style="position: relative; height: 300px;">
                <canvas id="weekdayChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Додаткові метрики -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="card">
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Всього продажів</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalSales) }}</p>
        </div>
        <div class="card">
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Продано товарів</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalQuantitySold) }}</p>
        </div>
        <div class="card">
            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Середній прибуток</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($avgProfitPerSale, 2) }} zł</p>
        </div>
    </div>

    <!-- Продажі за періоди -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="card">
            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Сьогодні</p>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $salesToday->count ?? 0 }} продажів</p>
            <p class="mt-1 text-sm text-gray-600">Дохід: {{ number_format($salesToday->revenue ?? 0, 2) }} zł</p>
            <p class="text-sm {{ ($salesToday->profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                Прибуток: {{ number_format($salesToday->profit ?? 0, 2) }} zł
            </p>
        </div>

        <div class="card">
            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Цей тиждень</p>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $salesThisWeek->count ?? 0 }} продажів</p>
            <p class="mt-1 text-sm text-gray-600">Дохід: {{ number_format($salesThisWeek->revenue ?? 0, 2) }} zł</p>
            <p class="text-sm {{ ($salesThisWeek->profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                Прибуток: {{ number_format($salesThisWeek->profit ?? 0, 2) }} zł
            </p>
        </div>

        <div class="card">
            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Цей місяць</p>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $salesThisMonth->count ?? 0 }} продажів</p>
            <p class="mt-1 text-sm text-gray-600">Дохід: {{ number_format($salesThisMonth->revenue ?? 0, 2) }} zł</p>
            <p class="text-sm {{ ($salesThisMonth->profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                Прибуток: {{ number_format($salesThisMonth->profit ?? 0, 2) }} zł
            </p>
        </div>

        <div class="card">
            <p class="text-xs font-medium text-gray-600 uppercase tracking-wide">Цей рік</p>
            <p class="mt-2 text-lg font-bold text-gray-900">{{ $salesThisYear->count ?? 0 }} продажів</p>
            <p class="mt-1 text-sm text-gray-600">Дохід: {{ number_format($salesThisYear->revenue ?? 0, 2) }} zł</p>
            <p class="text-sm {{ ($salesThisYear->profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                Прибуток: {{ number_format($salesThisYear->profit ?? 0, 2) }} zł
            </p>
        </div>
    </div>

    <!-- Топ товари -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 sm:mb-8">
        <!-- Топ товари за доходом -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Топ товари за доходом</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дохід</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прибуток</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topProductsByRevenue as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ number_format($product->revenue, 2) }} zł</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $product->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($product->profit, 2) }} zł</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Немає даних</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Топ товари за прибутком -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Топ товари за прибутком</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прибуток</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дохід</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topProductsByProfit as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $product->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($product->profit, 2) }} zł</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ number_format($product->revenue, 2) }} zł</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Немає даних</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Топ менеджери -->
    <div class="card mb-6 sm:mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Топ менеджери</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Менеджер</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Продажів</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дохід</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прибуток</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Середній чек</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topManagers as $manager)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $manager->name }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($manager->sales_count) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ number_format($manager->revenue, 2) }} zł</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold {{ $manager->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($manager->profit, 2) }} zł</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($manager->avg_revenue, 2) }} zł</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Немає даних</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Статистика по місяцях -->
    <div class="card mb-6 sm:mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Статистика по місяцях (останні 12 місяців)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Місяць</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Продажів</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дохід</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прибуток</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Продано товарів</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($monthlyStats as $stat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                @php
                                    $months = [
                                        '01' => 'Січень', '02' => 'Лютий', '03' => 'Березень', '04' => 'Квітень',
                                        '05' => 'Травень', '06' => 'Червень', '07' => 'Липень', '08' => 'Серпень',
                                        '09' => 'Вересень', '10' => 'Жовтень', '11' => 'Листопад', '12' => 'Грудень',
                                    ];
                                    $parts = explode('-', $stat->month);
                                    $monthName = $months[$parts[1] ?? ''] ?? $parts[1] ?? '';
                                @endphp
                                {{ $monthName }} {{ $parts[0] ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($stat->sales_count) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ number_format($stat->revenue, 2) }} zł</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold {{ $stat->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($stat->profit, 2) }} zł</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($stat->quantity_sold) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Немає даних</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Статистика по днях тижня -->
    <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Статистика по днях тижня</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">День тижня</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Продажів</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дохід</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Прибуток</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($weekdayStats as $stat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $stat->weekday }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($stat->sales_count) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">{{ number_format($stat->revenue, 2) }} zł</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold {{ $stat->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($stat->profit, 2) }} zł</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">Немає даних</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Дані для графіків
const salesByDay = @json($salesByDay);
const monthlyStats = @json($monthlyStats);
const topProductsByRevenue = @json($topProductsByRevenue->take(5));
const weekdayStats = @json($weekdayStats);

// Графік продажів за останні 30 днів
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesByDay.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit' });
        }),
        datasets: [{
            label: 'Дохід (zł)',
            data: salesByDay.map(item => parseFloat(item.revenue || 0)),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Прибуток (zł)',
            data: salesByDay.map(item => parseFloat(item.profit || 0)),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Графік по місяцях
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthLabels = monthlyStats.map(item => {
    const parts = item.month.split('-');
    const months = ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'];
    return months[parseInt(parts[1]) - 1] + ' ' + parts[0];
});

new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Дохід (zł)',
            data: monthlyStats.map(item => parseFloat(item.revenue || 0)),
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }, {
            label: 'Прибуток (zл)',
            data: monthlyStats.map(item => parseFloat(item.profit || 0)),
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        },
        layout: {
            padding: {
                top: 10,
                bottom: 10
            }
        }
    }
});

// Графік топ товарів за доходом
const productsCtx = document.getElementById('productsRevenueChart').getContext('2d');
new Chart(productsCtx, {
    type: 'bar',
    data: {
        labels: topProductsByRevenue.map(item => item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name),
        datasets: [{
            label: 'Дохід (zł)',
            data: topProductsByRevenue.map(item => parseFloat(item.revenue || 0)),
            backgroundColor: 'rgba(139, 92, 246, 0.8)',
            borderColor: 'rgb(139, 92, 246)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

// Графік по днях тижня
const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
new Chart(weekdayCtx, {
    type: 'bar',
    data: {
        labels: weekdayStats.map(item => item.weekday),
        datasets: [{
            label: 'Дохід (zł)',
            data: weekdayStats.map(item => parseFloat(item.revenue || 0)),
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(250, 204, 21, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderColor: [
                'rgb(239, 68, 68)',
                'rgb(245, 158, 11)',
                'rgb(250, 204, 21)',
                'rgb(34, 197, 94)',
                'rgb(59, 130, 246)',
                'rgb(139, 92, 246)',
                'rgb(236, 72, 153)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
@endsection

