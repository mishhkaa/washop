@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Панель менеджера</p>
    </div>

    <!-- Фільтр по періоду -->
    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Фільтр по періоду</h2>
        <form method="GET" action="{{ route('manager.dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label for="period_from">Дата від</label>
                <input type="date" id="period_from" name="period_from" value="{{ request('period_from') }}">
            </div>
            <div class="form-group">
                <label for="period_to">Дата до</label>
                <input type="date" id="period_to" name="period_to" value="{{ request('period_to') }}">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Фільтрувати</button>
                <a href="{{ route('manager.dashboard') }}" class="btn-secondary">Скинути</a>
            </div>
        </form>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Мої товари -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Мої товари</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $myProductsCount }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('manager.products.index') }}" class="mt-4 inline-block text-sm font-medium text-blue-600 hover:text-blue-700">
                Переглянути всі →
            </a>
        </div>

        <!-- Всього продажів -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Всього продажів</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalSales }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-xl">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <a href="{{ route('manager.sales.index') }}" class="mt-4 inline-block text-sm font-medium text-green-600 hover:text-green-700">
                Переглянути всі →
            </a>
        </div>

        <!-- Загальний дохід -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Загальний дохід</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalRevenue, 2) }} zł</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-xl">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Баланс -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Баланс</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($currentBalance ?? 0, 2) }} zł</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Швидкі дії -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 sm:mb-8">
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Швидкі дії</h2>
            <div class="space-y-3">
                <a href="{{ route('manager.sales.create') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg hover:from-blue-100 hover:to-indigo-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-600 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Створити продаж</p>
                            <p class="text-sm text-gray-600">Зареєструвати нову продажу</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('manager.products.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg hover:from-green-100 hover:to-emerald-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-600 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Мої товари</p>
                            <p class="text-sm text-gray-600">Переглянути доступні товари</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                <a href="{{ route('manager.sales.index') }}" class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg hover:from-purple-100 hover:to-pink-100 transition-colors">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-600 rounded-lg mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Мої продажі</p>
                            <p class="text-sm text-gray-600">Історія всіх продажів</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Останні продажі -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Останні продажі</h2>
                <a href="{{ route('manager.sales.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    Всі →
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentSales as $sale)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            @if($sale->is_combined)
                                <p class="font-medium text-gray-900">Комбінований продаж</p>
                                <p class="text-sm text-gray-500">
                                    @foreach($sale->saleItems as $item)
                                        {{ $item->product->name ?? '-' }} ({{ $item->quantity }} шт. × {{ number_format($item->sale_price, 2) }} zł)@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @else
                                <p class="font-medium text-gray-900">{{ $sale->product->name ?? '-' }}</p>
                                <p class="text-sm text-gray-500">{{ $sale->quantity }} шт. × {{ number_format($sale->sale_price ?? 0, 2) }} zł</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $sale->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            @if($sale->is_combined)
                                @php
                                    $total = 0;
                                    foreach($sale->saleItems as $item) {
                                        $total += ($item->sale_price ?? 0) * ($item->quantity ?? 0);
                                    }
                                @endphp
                                <p class="font-bold text-green-600">{{ number_format($total, 2) }} zł</p>
                            @else
                                <p class="font-bold text-green-600">{{ number_format(($sale->quantity ?? 0) * ($sale->sale_price ?? 0), 2) }} zł</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Немає продажів</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
