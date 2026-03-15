@extends('layouts.app')

@section('title', 'Клієнт #' . $client->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <header class="space-y-1">
        <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition">← До списку клієнтів</a>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $client->display_name }}</h1>
        <p class="text-sm text-gray-500">Клієнт #{{ $client->id }}</p>
    </header>

    @if(session('success'))
        <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 sm:gap-8 md:grid-cols-2">
        <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-base font-semibold text-gray-900">Інформація</h2>
            </div>
            <div class="p-6 space-y-6">
                <form action="{{ route('admin.clients.update', $client) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Ім'я</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $client->name) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>
                    <div>
                        <label for="cashback_percent" class="block text-sm font-medium text-gray-700 mb-2">% кешбеку від замовлення</label>
                        <input type="number" name="cashback_percent" id="cashback_percent" min="0" max="100" value="{{ old('cashback_percent', $client->cashback_percent) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>
                    <div>
                        <label for="cashback_balance" class="block text-sm font-medium text-gray-700 mb-2">Баланс кешбеку (zł) — встановити вручну</label>
                        <input type="number" name="cashback_balance" id="cashback_balance" min="0" step="0.01" value="{{ old('cashback_balance', $client->cashback_balance) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>
                    <div>
                        <label for="add_cashback" class="block text-sm font-medium text-gray-700 mb-2">Або додати до балансу (zł)</label>
                        <input type="number" name="add_cashback" id="add_cashback" min="0" step="0.01" value="{{ old('add_cashback') }}" placeholder="0" class="w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition shadow-sm">Зберегти</button>
                </form>
                <dl class="pt-5 border-t border-gray-100 space-y-5">
                    @if($client->telegram_username)
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Telegram нік</dt>
                            <dd class="text-gray-900 font-medium">@{{ $client->telegram_username }}</dd>
                        </div>
                    @endif
                    @if($client->telegram_user_id)
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Telegram ID</dt>
                            <dd class="text-gray-900 font-medium">{{ $client->telegram_user_id }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-base font-semibold text-gray-900">Кешбек</h2>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-3xl font-bold text-emerald-600 tabular-nums">{{ number_format((float) $client->cashback_balance, 2) }} zł</p>
                <p class="text-sm text-gray-500">Баланс спочатку 0 — кешбек надає тільки менеджер/адмін у формі зліва. Клієнт може використати його при оформленні замовлення.</p>
                @if($client->telegram_username)
                    <a href="https://t.me/{{ $client->telegram_username }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0088cc] text-white rounded-xl text-sm font-medium hover:bg-[#0077b5] transition shadow-sm mt-2">
                        Написати в Telegram
                    </a>
                @endif
            </div>
        </section>
    </div>

    <section class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-base font-semibold text-gray-900">Замовлення (останні)</h2>
        </div>
        <div class="p-6">
            @if($client->sales->count() > 0)
                <ul class="space-y-1">
                    @foreach($client->sales as $sale)
                        @php
                            $total = $sale->is_combined
                                ? $sale->saleItems->sum(fn ($i) => ($i->sale_price ?? 0) * $i->quantity)
                                : ($sale->sale_price ?? 0) * $sale->quantity;
                        @endphp
                        <li class="flex justify-between items-center py-3 border-b border-gray-50 last:border-0">
                            <a href="{{ route('admin.sales.show', $sale) }}" class="font-medium text-blue-600 hover:text-blue-700">#{{ $sale->id }}</a>
                            <span class="text-sm text-gray-500">{{ $sale->created_at->format('d.m.Y H:i') }}</span>
                            <span class="font-medium text-gray-900 tabular-nums">{{ number_format($total, 2) }} zł</span>
                        </li>
                    @endforeach
                </ul>
                @if($client->sales_count > 50)
                    <p class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-500">Показано останні 50 з {{ $client->sales_count }} замовлень.</p>
                @endif
            @else
                <p class="text-gray-500 py-4">Замовлень поки немає.</p>
            @endif
        </div>
    </section>
</div>
@endsection
