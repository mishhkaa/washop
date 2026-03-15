@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="crm-page crm-page__spacer">
    <header>
        <h1 class="crm-page__title">Панель керування</h1>
        <p class="crm-page__desc">Огляд системи та аналітика</p>
    </header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 crm-stack--lg">
        <div class="crm-panel hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between crm-panel__body">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Товари</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold text-green-600">{{ $stats['products_count'] }}</p>
                    <a href="{{ route('admin.products.index') }}" class="mt-3 inline-flex items-center text-xs sm:text-sm font-medium text-green-600 hover:text-green-800 group">
                        Переглянути
                        <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="ml-4 p-3 bg-green-100 rounded-xl">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="crm-panel hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between crm-panel__body">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Менеджери</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold text-purple-600">{{ $stats['managers_count'] }}</p>
                    <a href="{{ route('admin.managers.index') }}" class="mt-3 inline-flex items-center text-xs sm:text-sm font-medium text-purple-600 hover:text-purple-800 group">
                        Переглянути
                        <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="ml-4 p-3 bg-purple-100 rounded-xl">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="crm-panel hover:shadow-lg transition-all duration-200">
            <div class="flex items-center justify-between crm-panel__body">
                <div class="flex-1">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Продажі</p>
                    <p class="mt-2 text-2xl sm:text-3xl font-bold text-red-600">{{ $stats['sales_count'] }}</p>
                    <a href="{{ route('admin.sales.index') }}" class="mt-3 inline-flex items-center text-xs sm:text-sm font-medium text-red-600 hover:text-red-800 group">
                        Переглянути
                        <svg class="ml-1 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="ml-4 p-3 bg-red-100 rounded-xl">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Фінансова аналітика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="card bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-green-700 uppercase tracking-wide">Загальний дохід</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-green-800">{{ number_format($revenue, 2) }} zł</p>
                </div>
                <div class="p-3 bg-green-200 rounded-xl">
                    <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-blue-700 uppercase tracking-wide">Середній чек</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-blue-800">{{ number_format($avgSaleAmount, 2) }} zł</p>
                </div>
                <div class="p-3 bg-blue-200 rounded-xl">
                    <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-orange-700 uppercase tracking-wide">Продажі сьогодні</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold text-orange-800">{{ $salesToday }}</p>
                    <p class="mt-1 text-xs text-orange-600">Цього тижня: {{ $salesThisWeek }} | Цього місяця: {{ $salesThisMonth }}</p>
                </div>
                <div class="p-3 bg-orange-200 rounded-xl">
                    <svg class="w-8 h-8 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-emerald-50 to-emerald-100 border-2 border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-emerald-700 uppercase tracking-wide">Загальний прибуток</p>
                    <p class="mt-2 text-xl sm:text-2xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                        {{ number_format($totalProfit, 2) }} zł
                    </p>
                    @if($revenue > 0)
                    <p class="mt-1 text-xs text-emerald-600">
                        Маржинальність: {{ number_format(($totalProfit / $revenue) * 100, 2) }}%
                    </p>
                    @endif
                </div>
                <div class="p-3 bg-emerald-200 rounded-xl">
                    <svg class="w-8 h-8 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Топ товари -->
        <div class="card">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Топ-5 товарів за продажами</h2>
            <div class="space-y-3">
                @forelse($topProducts as $index => $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center flex-1 min-w-0">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-700 font-bold text-sm flex items-center justify-center mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                @if(isset($product->purchase_price))
                                <p class="text-xs text-gray-500">{{ number_format($product->purchase_price ?? 0, 2) }} zł</p>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $product->sales_count }} продажів
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Немає даних</p>
                @endforelse
            </div>
        </div>

        <!-- Топ менеджери -->
        <div class="card">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Топ-5 менеджерів</h2>
            <div class="space-y-3">
                @forelse($topManagers as $index => $manager)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center flex-1 min-w-0">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold text-sm flex items-center justify-center mr-3">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $manager->name }}</p>
                                <p class="text-xs text-gray-500">{{ $manager->email }}</p>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $manager->sales_count }} продажів
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Немає даних</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Останні продажі -->
    <div class="card mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Останні продажі</h2>
            <a href="{{ route('admin.sales.index') }}" class="mt-2 sm:mt-0 text-sm font-medium text-blue-600 hover:text-blue-800">
                Переглянути всі →
            </a>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Менеджер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $sale->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->display_product_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->manager->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->is_combined ? $sale->saleItems->sum('quantity') : $sale->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ number_format($sale->total_amount, 2) }} zł</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Продажі не знайдені</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3">
            @forelse($recentSales as $sale)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">{{ $sale->display_product_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">#{{ $sale->id }} • {{ $sale->manager->name ?? '-' }}</p>
                        </div>
                        <span class="text-sm font-bold text-green-600">{{ number_format($sale->total_amount, 2) }} zł</span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Кількість: {{ $sale->is_combined ? $sale->saleItems->sum('quantity') : $sale->quantity }}</span>
                        <span>{{ $sale->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center py-4">Продажі не знайдені</p>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
        <a href="{{ route('admin.products.create') }}" class="card hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1 border-2 border-dashed border-gray-300 hover:border-green-400 group">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-green-600">Додати товар</h3>
                    <p class="text-xs text-gray-500 mt-1">Створіть новий товар</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.sales.create') }}" class="card hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1 border-2 border-dashed border-gray-300 hover:border-red-400 group">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900 group-hover:text-red-600">Додати продаж</h3>
                    <p class="text-xs text-gray-500 mt-1">Зареєструйте новий продаж</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
