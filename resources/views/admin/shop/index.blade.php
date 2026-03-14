@extends('layouts.app')

@section('title', 'Магазин / ТГ-бот')

@section('content')
<div class="shop-page">
    <header class="shop-page__header">
        <h1 class="shop-page__title">Магазин та ТГ-бот</h1>
        <p class="shop-page__subtitle">Категорії, товари та замовлення з сайту й бота</p>
    </header>

    <nav class="shop-tabs" aria-label="Розділи">
        <a href="{{ route('admin.bot.index', ['tab' => 'categories']) }}" class="shop-tabs__link {{ ($tab ?? '') === 'categories' ? 'shop-tabs__link--active' : '' }}">
            Категорії
            <span class="shop-tabs__badge">{{ $shopCategories->count() }}</span>
        </a>
        <a href="{{ route('admin.bot.index', ['tab' => 'products']) }}" class="shop-tabs__link {{ ($tab ?? '') === 'products' ? 'shop-tabs__link--active' : '' }}">
            Товари
            <span class="shop-tabs__badge">{{ $botProducts->count() }}</span>
        </a>
        <a href="{{ route('admin.bot.index', ['tab' => 'orders']) }}" class="shop-tabs__link {{ ($tab ?? '') === 'orders' ? 'shop-tabs__link--active' : '' }}">
            Замовлення з бота
            <span class="shop-tabs__badge">{{ $botOrders->count() }}</span>
        </a>
    </nav>

    <div class="shop-content">
        @if(($tab ?? '') === 'categories')
            <div class="shop-toolbar">
                <p class="shop-toolbar__text">Категорії на головній сайту. Додайте фото та slug для фільтрації товарів.</p>
                <a href="{{ route('admin.shop.categories.create') }}" class="btn-primary shrink-0">+ Додати категорію</a>
            </div>
            <div class="shop-table-card overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Фото</th>
                            <th>Назва</th>
                            <th>Slug</th>
                            <th>Порядок</th>
                            <th class="text-right">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shopCategories as $cat)
                            <tr>
                                <td>
                                    @if($cat->image_path)
                                        <img src="{{ $cat->image_url }}" alt="" class="h-14 w-14 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="h-14 w-14 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">Немає</div>
                                    @endif
                                </td>
                                <td class="font-medium text-gray-900">{{ $cat->name }}</td>
                                <td class="text-gray-500 font-mono text-sm">{{ $cat->slug }}</td>
                                <td class="text-gray-500">{{ $cat->sort_order }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2 flex-wrap">
                                        <a href="{{ route('admin.shop.categories.edit', $cat) }}" class="shop-btn shop-btn--edit">Редагувати / фото</a>
                                        <form action="{{ route('admin.shop.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Видалити категорію?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="shop-btn shop-btn--danger">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="shop-empty">
                                    <p class="mb-3">Категорій ще немає.</p>
                                    <a href="{{ route('admin.shop.categories.create') }}" class="text-blue-600 font-medium hover:underline">Додати першу категорію</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif(($tab ?? '') === 'products')
            <div class="shop-toolbar">
                <p class="shop-toolbar__text">Товари, які показуються на сайті та в боті. Редагуйте фото та дані.</p>
                <a href="{{ route('admin.products.create') }}" class="btn-primary shrink-0">+ Додати товар</a>
            </div>
            <div class="shop-table-card overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Фото</th>
                            <th>Назва</th>
                            <th>Ціна</th>
                            <th>Склад</th>
                            <th class="text-right">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($botProducts as $product)
                            <tr>
                                <td>
                                    @if($product->image_path)
                                        <img src="{{ $product->image_url }}" alt="" class="h-14 w-14 rounded-lg object-cover border border-gray-200">
                                    @else
                                        <div class="h-14 w-14 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">—</div>
                                    @endif
                                </td>
                                <td class="font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="text-gray-600">{{ number_format($product->purchase_price ?? 0, 2) }} zł</td>
                                <td class="text-gray-600">{{ $product->quantity ?? 0 }} шт.</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="shop-btn shop-btn--edit">Редагувати / фото</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="shop-empty">
                                    Немає товарів для магазину. Увімкніть «Доступний на сайті» у <a href="{{ route('admin.products.index') }}" class="text-blue-600 hover:underline">Товарах</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="shop-toolbar">
                <p class="shop-toolbar__text">Замовлення з сайту та Telegram-бота. Відкрийте деталі, щоб побачити склад та перейти в чат з клієнтом.</p>
            </div>
            <div class="shop-table-card overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Товари</th>
                            <th>Нік / Telegram</th>
                            <th>Сума</th>
                            <th>Дата</th>
                            <th class="text-right">Дії</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($botOrders as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="font-semibold text-blue-600 hover:text-blue-800 no-underline">#{{ $sale->id }}</a>
                                </td>
                                <td class="text-gray-700 max-w-xs">
                                    @foreach($sale->saleItems as $item)
                                        {{ $item->product->name ?? '-' }} × {{ $item->quantity }} ({{ number_format($item->sale_price ?? 0, 2) }} zł)@if(!$loop->last)<br>@endif
                                    @endforeach
                                </td>
                                <td>
                                    @if($sale->telegram_username)
                                        <a href="https://t.me/{{ $sale->telegram_username }}" target="_blank" rel="noopener" class="text-[#0088cc] hover:underline font-medium">@{{ $sale->telegram_username }}</a>
                                    @elseif($sale->telegram_user_id)
                                        <span class="text-gray-500" title="ID">{{ $sale->telegram_user_id }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="font-semibold text-green-600 whitespace-nowrap">
                                    @php $total = 0; foreach($sale->saleItems as $item) { $total += ($item->sale_price ?? 0) * ($item->quantity ?? 0); } @endphp
                                    {{ number_format($total, 2) }} zł
                                </td>
                                <td class="text-gray-500 text-sm whitespace-nowrap">{{ $sale->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2 flex-wrap">
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="shop-btn shop-btn--edit">Деталі</a>
                                        @if($sale->telegram_username)
                                            <a href="https://t.me/{{ $sale->telegram_username }}" target="_blank" rel="noopener" class="shop-btn shop-btn--tg">Написати в ТГ</a>
                                        @elseif($sale->telegram_user_id)
                                            <a href="tg://user?id={{ $sale->telegram_user_id }}" target="_blank" rel="noopener" class="shop-btn shop-btn--tg">Написати в ТГ</a>
                                        @endif
                                        <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" class="inline" onsubmit="return confirm('Видалити замовлення?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="shop-btn shop-btn--danger">Видалити</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="shop-empty">Замовлень з бота поки немає.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
