@extends('layouts.app')

@section('title', 'Замовлення #' . $sale->id)

@section('content')
<div class="crm-page crm-page__spacer">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">{{ session('success') }}</div>
    @endif
    <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
        <div class="space-y-2">
            <a href="{{ request()->headers->get('referer') ?: route('admin.bot.index', ['tab' => 'orders']) }}" class="crm-page__back">← Назад</a>
            <h1 class="crm-page__title">Замовлення #{{ $sale->id }}</h1>
            <p class="text-sm text-gray-500">
                {{ $sale->created_at->format('d.m.Y H:i') }}
                <span class="text-gray-400">·</span>
                {{ $sale->source === 'bot' ? 'З сайту / ТГ-бота' : 'CRM' }}
                @if(isset($sale->status))
                    <span class="text-gray-400">·</span>
                    @if($sale->status === 'pending')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">Очікує</span>
                    @elseif($sale->status === 'accepted')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Прийнято</span>
                    @elseif($sale->status === 'completed')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Виконано</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">Скасовано</span>
                    @endif
                @endif
            </p>
        </div>
        @if($sale->source === 'bot' && ($sale->telegram_user_id || $sale->telegram_username))
            <div class="flex-shrink-0">
            @if($sale->telegram_username)
                <a href="https://t.me/{{ $sale->telegram_username }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0088cc] text-white rounded-xl font-medium text-sm hover:bg-[#0077b5] transition shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Написати в Telegram @{{ $sale->telegram_username }}
                </a>
            @elseif($sale->telegram_user_id)
                <a href="tg://user?id={{ $sale->telegram_user_id }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0088cc] text-white rounded-xl font-medium text-sm hover:bg-[#0077b5] transition shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    Написати в Telegram (ID {{ $sale->telegram_user_id }})
                </a>
            @endif
            </div>
        @endif
    </header>

    {{-- Дві колонки: достатній проміжок, щоб контент не наїжджав --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
        {{-- Ліва колонка: склад замовлення --}}
        <section class="crm-panel min-w-0">
            <div class="crm-panel__header">
                <h2 class="crm-panel__title">Склад замовлення</h2>
            </div>
            <div class="crm-panel__body">
                <ul class="space-y-5">
                    @if($sale->is_combined && $sale->saleItems->count() > 0)
                        @foreach($sale->saleItems as $item)
                            <li class="flex justify-between items-baseline gap-6">
                                <span class="text-gray-700 min-w-0">{{ $item->display_name }} <span class="text-gray-400">× {{ $item->quantity }}</span></span>
                                <span class="font-medium text-gray-900 tabular-nums shrink-0">{{ number_format(($item->sale_price ?? 0) * $item->quantity, 2) }} zł</span>
                            </li>
                        @endforeach
                    @else
                        <li class="flex justify-between items-baseline gap-6">
                            <span class="text-gray-700 min-w-0">{{ $sale->product->name ?? '—' }} <span class="text-gray-400">× {{ $sale->quantity }}</span></span>
                            <span class="font-medium text-gray-900 tabular-nums shrink-0">{{ number_format(($sale->sale_price ?? 0) * $sale->quantity, 2) }} zł</span>
                        </li>
                    @endif
                </ul>
                @php
                    $total = $sale->is_combined
                        ? $sale->saleItems->sum(fn ($i) => ($i->sale_price ?? 0) * $i->quantity)
                        : ($sale->sale_price ?? 0) * $sale->quantity;
                @endphp
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-between items-baseline gap-6 text-lg font-semibold">
                    <span class="text-gray-700">Разом</span>
                    <span class="text-emerald-600 tabular-nums shrink-0">{{ number_format($total, 2) }} zł</span>
                </div>
            </div>
        </section>

        {{-- Права колонка: клієнт і менеджер --}}
        <div class="min-w-0 space-y-8">
            {{-- Клієнт / Джерело --}}
            <section class="crm-panel">
                <div class="crm-panel__header">
                    <h2 class="crm-panel__title">Клієнт / Джерело</h2>
                </div>
                <div class="crm-panel__body">
                    <dl class="space-y-6">
                        @if($sale->client)
                            @if($sale->client->telegram_username)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Telegram нік</dt>
                                    <dd class="text-gray-900 font-medium">@{{ $sale->client->telegram_username }}</dd>
                                </div>
                            @endif
                            @if($sale->client->telegram_user_id)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Telegram ID</dt>
                                    <dd class="text-gray-900 font-medium">{{ $sale->client->telegram_user_id }}</dd>
                                </div>
                            @endif
                            @if($sale->client->name)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Ім'я</dt>
                                    <dd class="text-gray-900">{{ $sale->client->name }}</dd>
                                </div>
                            @endif
                            @if($sale->client->phone)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Телефон</dt>
                                    <dd class="text-gray-900">{{ $sale->client->phone }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Кешбек</dt>
                                <dd class="text-emerald-600 font-semibold">{{ number_format((float) $sale->client->cashback_balance, 2) }} zł</dd>
                            </div>
                            <div class="pt-2">
                                <a href="{{ route('admin.clients.show', $sale->client) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Відкрити картку клієнта →</a>
                            </div>
                        @else
                            @if($sale->source === 'bot')
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Telegram нік</dt>
                                    <dd class="text-gray-900">{{ $sale->telegram_username ? '@' . $sale->telegram_username : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Telegram ID</dt>
                                    <dd class="text-gray-900">{{ $sale->telegram_user_id ?? '—' }}</dd>
                                </div>
                            @endif
                        @endif
                        <div>
                            <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Замовлення створено</dt>
                            <dd class="text-gray-900">{{ $sale->created_at->format('d.m.Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Доставка --}}
            @if($sale->delivery_method)
            <section class="crm-panel">
                <div class="crm-panel__header">
                    <h2 class="crm-panel__title">Доставка</h2>
                </div>
                <div class="crm-panel__body">
                    <dl class="space-y-6">
                        <div>
                            <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Спосіб</dt>
                            <dd class="text-gray-900">{{ $sale->delivery_method === 'paczkomat' ? 'Paczkomat InPost' : 'Особистий відбір' }}</dd>
                        </div>
                        @if($sale->delivery_method === 'paczkomat' && $sale->delivery_paczkomat_code)
                            <div>
                                <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Код paczkomatu</dt>
                                <dd class="text-gray-900 font-mono">{{ $sale->delivery_paczkomat_code }}</dd>
                            </div>
                        @endif
                        @if($sale->delivery_method === 'osobisty_odbior')
                            @if($sale->delivery_pickup_name)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Ім'я</dt>
                                    <dd class="text-gray-900">{{ $sale->delivery_pickup_name }}</dd>
                                </div>
                            @endif
                            @if($sale->delivery_pickup_phone)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Телефон</dt>
                                    <dd class="text-gray-900">{{ $sale->delivery_pickup_phone }}</dd>
                                </div>
                            @endif
                            @if($sale->delivery_pickup_district)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Район</dt>
                                    <dd class="text-gray-900">{{ $sale->delivery_pickup_district }}</dd>
                                </div>
                            @endif
                            @if(isset($sale->delivery_pickup_day) && $sale->delivery_pickup_day)
                                <div>
                                    <dt class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">День забрання</dt>
                                    <dd class="text-gray-900">{{ $sale->delivery_pickup_day }}</dd>
                                </div>
                            @endif
                        @endif
                    </dl>
                </div>
            </section>
            @endif

            {{-- Дії з замовленням: підтвердження / відхилення / виконано --}}
            @if(isset($sale->status))
            <section class="crm-panel">
                <div class="crm-panel__header">
                    <h2 class="crm-panel__title">Дії з замовленням</h2>
                </div>
                <div class="crm-panel__body space-y-4">
                    @if(($sale->status ?? '') === 'pending')
                        <p class="text-sm text-gray-600 mb-4">Підтвердьте замовлення (спишеться залишок зі складу) або відхиліть.</p>
                        <div class="flex flex-wrap gap-3">
                            <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Підтвердити замовлення
                                </button>
                            </form>
                            <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="inline" onsubmit="return confirm('Відхилити це замовлення?');">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-500 text-white rounded-xl text-sm font-semibold hover:bg-gray-600 transition shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Відхилити замовлення
                                </button>
                            </form>
                        </div>
                    @elseif(($sale->status ?? '') === 'accepted')
                        <p class="text-sm text-gray-600 mb-4">Замовлення прийнято. Позначте виконаним, коли клієнт отримає товар.</p>
                        <div class="flex flex-wrap gap-3">
                            <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Позначити виконаним
                                </button>
                            </form>
                            <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="inline" onsubmit="return confirm('Скасувати замовлення? Залишки будуть повернені на склад.');">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                                    Скасувати
                                </button>
                            </form>
                        </div>
                    @elseif(($sale->status ?? '') === 'completed')
                        <p class="text-sm text-emerald-700 font-medium">Замовлення виконано.</p>
                        <details class="mt-3">
                            <summary class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">Змінити статус</summary>
                            <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="mt-3 flex flex-wrap items-center gap-3">
                                @csrf
                                <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="accepted">Прийнято</option>
                                    <option value="cancelled">Скасовано</option>
                                </select>
                                <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300">Зберегти</button>
                            </form>
                        </details>
                    @else
                        <p class="text-sm text-gray-600">Замовлення скасовано.</p>
                        <form action="{{ route('admin.sales.update-status', $sale) }}" method="POST" class="mt-3 flex flex-wrap items-center gap-3">
                            @csrf
                            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="pending">Очікує</option>
                                <option value="accepted">Прийнято</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Змінити статус</button>
                        </form>
                    @endif
                </div>
            </section>
            @endif

            {{-- Менеджер --}}
            <section class="crm-panel">
                <div class="crm-panel__header">
                    <h2 class="crm-panel__title">Менеджер</h2>
                </div>
                <div class="crm-panel__body">
                    <form action="{{ route('admin.sales.assign-manager', $sale) }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">Призначити менеджера</label>
                            <select name="manager_id" id="manager_id" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                                <option value="">— Не призначено —</option>
                                @foreach($managers as $m)
                                    <option value="{{ $m->id }}" {{ (int) $sale->manager_id === (int) $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                            Зберегти
                        </button>
                    </form>
                    @if($sale->manager)
                        <p class="mt-6 pt-5 border-t border-gray-100 text-sm text-gray-600">
                            Поточний: <span class="font-medium text-gray-900">{{ $sale->manager->name }}</span>
                        </p>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <footer class="crm-actions crm-actions--end flex-wrap gap-6">
        <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('Видалити це замовлення?');" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="crm-link-btn--danger">Видалити замовлення</button>
        </form>
        <a href="{{ route('admin.bot.index', ['tab' => 'orders']) }}" class="crm-link-btn">До списку замовлень</a>
        <a href="{{ route('admin.sales.index') }}" class="crm-link-btn">Всі продажі</a>
    </footer>
</div>
@endsection
