@extends('layouts.app')

@section('title', 'Клієнти')

@section('content')
<div class="crm-page crm-page__spacer">
    <header>
        <h1 class="crm-page__title">Клієнти</h1>
        <p class="crm-page__desc">Усі клієнти з сайту та ТГ-бота, кешбек</p>
    </header>

    @if(session('success'))
        <div class="crm-alert--success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Нік, ID, ім'я, телефон..." class="flex-1 min-w-0 rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
        <button type="submit" class="btn-primary flex-shrink-0">Пошук</button>
    </form>

    <section class="crm-panel">
        <div class="crm-panel__body">
            <div class="crm-table-wrap">
                <table class="crm-table">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Клієнт</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Telegram</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Контакт</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Кешбек</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Замовлень</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Дії</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $client->display_name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">#{{ $client->id }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($client->telegram_username)<span class="font-medium">&#64;{{ $client->telegram_username }}</span>@else <span class="text-gray-400">—</span> @endif
                                @if($client->telegram_user_id)<br><span class="text-gray-400 text-xs">ID {{ $client->telegram_user_id }}</span>@endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $client->phone ?? '—' }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-600 tabular-nums">{{ number_format((float) $client->cashback_balance, 2) }} zł</td>
                            <td class="px-6 py-4 text-right text-sm text-gray-500 tabular-nums">{{ $client->sales_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.clients.show', $client) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Відкрити</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">Клієнтів поки немає. Вони з'являться після замовлень з сайту або бота.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
        @if($clients->hasPages())
            <div class="crm-panel__body crm-panel__body--compact border-t border-gray-100">
                {{ $clients->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
