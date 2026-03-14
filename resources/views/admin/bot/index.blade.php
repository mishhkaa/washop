@extends('layouts.app')

@section('title', 'Telegram бот')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Telegram бот / shop_site</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Замовлення з бота та товари для бота</p>
    </div>

    <!-- Вкладки -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-4" aria-label="Tabs">
            <a href="{{ route('admin.bot.index', ['tab' => 'orders']) }}" class="py-3 px-1 border-b-2 font-medium text-sm {{ ($tab ?? '') === 'orders' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Замовлення з бота
                <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $botOrders->count() }}</span>
            </a>
            <a href="{{ route('admin.bot.index', ['tab' => 'products']) }}" class="py-3 px-1 border-b-2 font-medium text-sm {{ ($tab ?? '') === 'products' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Товари з бота
                <span class="ml-1 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ $botProducts->count() }}</span>
            </a>
        </nav>
    </div>

    @if(($tab ?? '') === 'products')
        <!-- Товари з бота -->
        <div class="card">
            <p class="text-sm text-gray-600 mb-4">Товари з позначкою «Доступний у боті / на сайті» — вони показуються в боті та приймаються в замовленнях. Редагувати можна в розділі <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:underline">Товари</a>.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ціна закуп.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Менеджер</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($botProducts as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $product->id }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($product->purchase_price ?? 0, 2) }} zł</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $product->manager->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:underline">Редагувати</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Немає товарів для бота. Увімкніть «Доступний у боті» в <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:underline">Товарах</a>.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <!-- Замовлення з бота -->
        <div class="card">
            <p class="text-sm text-gray-600 mb-4">Усі замовлення, що прийшли з сайту або Telegram-бота.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товари</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telegram ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сума</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($botOrders as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $sale->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @foreach($sale->saleItems as $item)
                                        {{ $item->product->name ?? '-' }} × {{ $item->quantity }} ({{ number_format($item->sale_price ?? 0, 2) }} zł)<br>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->telegram_user_id ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                    @php
                                        $total = 0;
                                        foreach($sale->saleItems as $item) {
                                            $total += ($item->sale_price ?? 0) * ($item->quantity ?? 0);
                                        }
                                    @endphp
                                    {{ number_format($total, 2) }} zł
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" class="inline" onsubmit="return confirm('Видалити це замовлення?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Видалити</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Замовлень з бота поки немає.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
