@extends('layouts.app')

@section('title', 'Мої продажі')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Мої продажі</h1>
            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Історія всіх продажів</p>
        </div>
        <a href="{{ route('manager.sales.create') }}" class="btn-primary w-full sm:w-auto">
            + Створити продаж
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ціна продажу</th>
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
                                <form action="{{ route('manager.sales.destroy', $sale->id) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені, що хочете видалити цей продаж? Кількість товару буде повернено на склад.');">
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">Продажі не знайдено</p>
                                <a href="{{ route('manager.sales.create') }}" class="mt-4 inline-block btn-primary">Створити перший продаж</a>
                            </td>
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
                                    <span class="font-medium">{{ $sale->quantity }} шт.</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Ціна продажу:</span>
                                    <span class="font-medium">{{ number_format($sale->sale_price ?? 0, 2) }} zł</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Сума:</span>
                                    <span class="font-bold text-green-600">{{ number_format(($sale->quantity ?? 0) * ($sale->sale_price ?? 0), 2) }} zł</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                <span class="text-gray-500">Дата:</span>
                                <span class="text-gray-600">{{ $sale->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="ml-4">
                        <form action="{{ route('manager.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Ви впевнені, що хочете видалити цей продаж? Кількість товару буде повернено на склад.');">
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
                <p class="text-sm text-gray-500">Продажі не знайдено</p>
                <a href="{{ route('manager.sales.create') }}" class="mt-4 inline-block btn-primary">Створити перший продаж</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

