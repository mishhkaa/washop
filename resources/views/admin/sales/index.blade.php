@extends('layouts.app')

@section('title', 'Продажі')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Продажі</h1>
            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Перегляд всіх продажів</p>
        </div>
        <a href="{{ route('admin.sales.create') }}" class="btn-primary w-full sm:w-auto">
            + Додати продаж
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Фільтри -->
    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Фільтри</h2>
        <form method="GET" action="{{ route('admin.sales.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="form-group">
                <label for="manager_id">Менеджер</label>
                <select id="manager_id" name="manager_id">
                    <option value="">Всі менеджери</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="product_id">Товар</label>
                <select id="product_id" name="product_id">
                    <option value="">Всі товари</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="date_from">Дата від</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="form-group">
                <label for="date_to">Дата до</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="form-group">
                <label for="source">Джерело</label>
                <select id="source" name="source">
                    <option value="">Всі</option>
                    <option value="crm" {{ request('source') === 'crm' ? 'selected' : '' }}>CRM</option>
                    <option value="bot" {{ request('source') === 'bot' ? 'selected' : '' }}>З бота / сайту</option>
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary">Фільтрувати</button>
                <a href="{{ route('admin.sales.index') }}" class="btn-secondary">Очистити</a>
            </div>
        </form>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Менеджер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ціна</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $sale->id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                @if($sale->is_combined)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800 mb-1">Комбінований</span><br>
                                    @foreach($sale->saleItems as $item)
                                        {{ $item->product->name ?? '-' }} ({{ $item->quantity }} шт.)<br>
                                    @endforeach
                                @else
                                    {{ $sale->product->name ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($sale->profit_to_admin)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Адмін
                                    </span>
                                @else
                                    {{ $sale->manager->name ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($sale->is_combined)
                                    {{ $sale->saleItems->sum('quantity') }}
                                @else
                                    {{ $sale->quantity }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($sale->is_combined)
                                    @foreach($sale->saleItems as $item)
                                        {{ number_format($item->sale_price ?? 0, 2) }} zł<br>
                                    @endforeach
                                @else
                                    {{ number_format($sale->sale_price ?? 0, 2) }} zł
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                @if($sale->is_combined)
                                    @php
                                        $total = 0;
                                        foreach($sale->saleItems as $item) {
                                            $total += ($item->sale_price ?? 0) * ($item->quantity ?? 0);
                                        }
                                    @endphp
                                    {{ number_format($total, 2) }} zł
                                @else
                                    {{ number_format(($sale->quantity ?? 0) * ($sale->sale_price ?? 0), 2) }} zł
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені, що хочете видалити цей продаж? Кількість товару буде повернено на склад.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium px-3 py-1 rounded">
                                        Видалити
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">Продажі не знайдені</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4">
                @forelse($sales as $sale)
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-xs font-medium text-gray-500 mr-2">#{{ $sale->id }}</span>
                            @if($sale->is_combined)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800 mr-2">Комбінований</span>
                                <h3 class="text-lg font-semibold text-gray-900">Комбінований продаж</h3>
                            @else
                                <h3 class="text-lg font-semibold text-gray-900">{{ $sale->product->name ?? '-' }}</h3>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Менеджер:</span>
                                <span class="font-medium">
                                    @if($sale->profit_to_admin)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Адмін
                                        </span>
                                    @else
                                        {{ $sale->manager->name ?? '-' }}
                                    @endif
                                </span>
                            </div>
                            @if($sale->is_combined)
                                <div class="mt-2 space-y-1">
                                    @foreach($sale->saleItems as $item)
                                        <div class="flex justify-between text-sm bg-gray-50 p-2 rounded">
                                            <span class="text-gray-700">{{ $item->product->name ?? '-' }} ({{ $item->quantity }} шт.)</span>
                                            <span class="font-medium">{{ number_format($item->sale_price ?? 0, 2) }} zł</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                    <span class="text-gray-700 font-medium">Загальна сума:</span>
                                    @php
                                        $total = 0;
                                        foreach($sale->saleItems as $item) {
                                            $total += ($item->sale_price ?? 0) * ($item->quantity ?? 0);
                                        }
                                    @endphp
                                    <span class="font-bold text-green-600">{{ number_format($total, 2) }} zł</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Кількість:</span>
                                    <span class="font-medium">{{ $sale->quantity }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Ціна:</span>
                                    <span class="font-medium">{{ number_format($sale->sale_price ?? 0, 2) }} zł</span>
                                </div>
                                <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                    <span class="text-gray-700 font-medium">Сума:</span>
                                    <span class="font-bold text-green-600">{{ number_format(($sale->quantity ?? 0) * ($sale->sale_price ?? 0), 2) }} zł</span>
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">{{ $sale->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                    </div>
                    <div class="ml-4">
                        <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Ви впевнені, що хочете видалити цей продаж? Кількість товару буде повернено на склад.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium text-sm px-3 py-1 rounded">
                                Видалити
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-sm text-gray-500">Продажі не знайдені</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
