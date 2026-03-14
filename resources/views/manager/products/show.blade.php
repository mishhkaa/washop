@extends('layouts.app')

@section('title', 'Деталі товару')

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Детальна інформація про товар</p>
            </div>
            <a href="{{ route('manager.products.index') }}" class="btn-secondary">
                ← Назад до товарів
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Основна інформація -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Основна інформація</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">ID товару</label>
                    <p class="mt-1 text-base font-semibold text-gray-900">#{{ $product->id }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Назва</label>
                    <p class="mt-1 text-base text-gray-900">{{ $product->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Кількість на складі</label>
                    <p class="mt-1 text-base font-semibold text-gray-900">{{ $product->quantity ?? 0 }} шт.</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Статус</label>
                    <p class="mt-1">
                        @if($product->manager_id === auth()->id())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Призначено мені
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Для всіх менеджерів
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Дії -->
        <div class="card">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Дії</h2>
            <div class="space-y-3">
                <a href="{{ route('manager.sales.create', ['product_id' => $product->id]) }}" class="btn-primary w-full text-center block">
                    Створити продаж
                </a>
                <a href="{{ route('manager.products.index') }}" class="btn-secondary w-full text-center block">
                    Повернутися до списку
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

