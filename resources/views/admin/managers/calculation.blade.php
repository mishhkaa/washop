@extends('layouts.app')

@section('title', 'Розрахунок: ' . $manager->name)

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Розрахунок</h1>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">{{ $manager->name }} ({{ $manager->email }})</p>
            </div>
            <a href="{{ route('admin.managers.index') }}" class="btn-secondary">
                ← Назад до менеджерів
            </a>
        </div>
    </div>

    <!-- Кнопки дій -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <a href="{{ route('admin.managers.payments.create', $manager) }}" class="card hover:shadow-lg transition-all cursor-pointer bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100">
            <div class="flex items-center">
                <div class="p-3 bg-blue-600 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Розрахувати</h3>
                    <p class="text-sm text-gray-600">Додати нову виплату</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.managers.payments', $manager) }}" class="card hover:shadow-lg transition-all cursor-pointer bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100">
            <div class="flex items-center">
                <div class="p-3 bg-green-600 rounded-lg mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Історія виплат</h3>
                    <p class="text-sm text-gray-600">Переглянути всі виплати</p>
                </div>
            </div>
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

    <!-- Фільтр по періоду -->
    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Фільтр по періоду</h2>
        <form method="GET" action="{{ route('admin.managers.calculation', $manager) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label for="period_from">Дата від</label>
                <input type="date" id="period_from" name="period_from" value="{{ request('period_from') }}">
            </div>
            <div class="form-group">
                <label for="period_to">Дата до</label>
                <input type="date" id="period_to" name="period_to" value="{{ request('period_to') }}">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Фільтрувати</button>
                <a href="{{ route('admin.managers.calculation', $manager) }}" class="btn-secondary">Скинути</a>
            </div>
        </form>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Баланс менеджера</p>
                    <p class="text-2xl font-bold text-blue-900 mt-2">{{ number_format($currentBalance ?? 0, 2) }} zł</p>
                </div>
                <div class="p-3 bg-blue-200 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-green-50 to-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Загальний дохід</p>
                    <p class="text-2xl font-bold text-green-900 mt-2">{{ number_format($totalRevenue ?? 0, 2) }} zł</p>
                </div>
                <div class="p-3 bg-green-200 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-purple-50 to-purple-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600">Чистий прибуток</p>
                    <p class="text-2xl font-bold text-purple-900 mt-2">{{ number_format($managerNetProfit ?? 0, 2) }} zł</p>
                    <p class="text-xs text-purple-600 mt-1">Прибуток менеджера</p>
                </div>
                <div class="p-3 bg-purple-200 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card bg-gradient-to-br from-orange-50 to-orange-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600">Загальний прибуток</p>
                    <p class="text-2xl font-bold text-orange-900 mt-2">{{ number_format($totalProfit ?? 0, 2) }} zł</p>
                    <p class="text-xs text-orange-600 mt-1">Всього прибутку</p>
                </div>
                <div class="p-3 bg-orange-200 rounded-lg">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Останні виплати -->
    <div class="card">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Останні виплати</h2>
            <a href="{{ route('admin.managers.payments', $manager) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                Всі виплати →
            </a>
        </div>

        @php
            $recentPayments = \App\Models\Payment::where('manager_id', $manager->id)
                ->orderBy('payment_date', 'desc')
                ->limit(5)
                ->get();
        @endphp

        @if($recentPayments->count() > 0)
            <div class="space-y-3">
                @foreach($recentPayments as $payment)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ number_format($payment->amount, 2) }} zł</p>
                            <p class="text-sm text-gray-500">
                                {{ $payment->period_from->format('d.m.Y') }} - {{ $payment->period_to->format('d.m.Y') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Виплата: {{ $payment->payment_date->format('d.m.Y') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Виплачено
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Немає виплат</p>
        @endif
    </div>
</div>
@endsection
