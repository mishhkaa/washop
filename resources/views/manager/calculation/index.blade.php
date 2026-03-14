@extends('layouts.app')

@section('title', 'Мій розрахунок')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Мій розрахунок</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Баланс та історія виплат</p>
    </div>

    <!-- Пояснення -->
    <div class="card mb-6 p-4 bg-blue-50 border border-blue-100">
        <div class="flex items-start">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Як формується баланс</p>
                <ul class="list-disc list-inside space-y-0.5">
                    <li><strong>Продажі</strong> — сума кожного продажу (ціна продажу × кількість) <strong>додається</strong> до балансу (+).</li>
                    <li><strong>Розрахунок (виплата)</strong> — коли адмін проводить вам виплату, сума <strong>віднімається</strong> з балансу (−).</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Поточний баланс -->
    <div class="card mb-6 bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-green-700">Поточний баланс</p>
                <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($currentBalance, 2) }} zł</p>
            </div>
            <div class="p-4 bg-green-200 rounded-xl">
                <svg class="w-10 h-10 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Історія виплат (розрахунків) -->
    <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Історія виплат (розрахунків)</h2>
        <p class="text-sm text-gray-500 mb-4">Тут відображаються виплати, які адмін провів вам. Кожна виплата зменшувала ваш баланс.</p>

        @if($payments->count() > 0)
            <div class="space-y-3">
                @foreach($payments as $payment)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div>
                            <p class="font-semibold text-gray-900">{{ number_format($payment->amount, 2) }} zł</p>
                            <p class="text-sm text-gray-500">
                                Період: {{ $payment->period_from->format('d.m.Y') }} – {{ $payment->period_to->format('d.m.Y') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Дата виплати: {{ $payment->payment_date->format('d.m.Y') }}</p>
                            @if($payment->notes)
                                <p class="text-xs text-gray-500 mt-1">{{ $payment->notes }}</p>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Виплачено
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Поки немає виплат. Коли адмін проведе вам розрахунок, він з’явиться тут.</p>
        @endif
    </div>
</div>
@endsection
