@extends('layouts.app')

@section('title', 'Склад менеджера: ' . $manager->name)

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Склад менеджера</h1>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">{{ $manager->name }} ({{ $manager->email }})</p>
            </div>
            <a href="{{ route('admin.managers.index') }}" class="btn-secondary">
                ← Назад до менеджерів
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Товарів на складі</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $productsCount }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Загальна кількість</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalQuantity, 0) }} шт.</p>
                </div>
                <div class="p-3 bg-green-100 rounded-xl">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Загальна вартість</p>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalValue, 2) }} zł</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-xl">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблиця товарів -->
    <div class="card">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900">Товари на складі</h2>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ціна закупівлі</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Вартість</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $product->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ number_format($product->purchase_price ?? 0, 2) }} zł</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity ?? 0 }} шт.</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                                {{ number_format(($product->purchase_price ?? 0) * ($product->quantity ?? 0), 2) }} zł
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">У цього менеджера немає товарів на складі</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4">
            @forelse($products as $product)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <span class="text-xs font-medium text-gray-500 mr-2">#{{ $product->id }}</span>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Ціна закупівлі:</span>
                                    <span class="font-bold text-green-600">{{ number_format($product->purchase_price ?? 0, 2) }} zł</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Кількість на складі:</span>
                                    <span class="font-medium">{{ $product->quantity ?? 0 }} шт.</span>
                                </div>
                                <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                    <span class="text-gray-500">Вартість:</span>
                                    <span class="font-bold text-blue-600">
                                        {{ number_format(($product->purchase_price ?? 0) * ($product->quantity ?? 0), 2) }} zł
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <p class="text-sm text-gray-500">У цього менеджера немає товарів на складі</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
