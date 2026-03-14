@extends('layouts.app')

@section('title', 'Виплати: ' . $manager->name)

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Історія виплат</h1>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">{{ $manager->name }} ({{ $manager->email }})</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.managers.payments.create', $manager) }}" class="btn-primary">
                    + Додати виплату
                </a>
                <a href="{{ route('admin.managers.calculation', $manager) }}" class="btn-secondary">
                    ← Назад
                </a>
            </div>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Сума</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дата виплати</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Період</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Примітки</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $payment->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ number_format($payment->amount, 2) }} zł</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $payment->payment_date->format('d.m.Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $payment->period_from->format('d.m.Y') }} - {{ $payment->period_to->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->notes ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.managers.payments.edit', [$manager, $payment]) }}" class="btn-edit">Редагувати</a>
                                    <form action="{{ route('admin.managers.payments.destroy', [$manager, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Ви впевнені, що хочете видалити цю виплату?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">Видалити</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">Виплати не знайдено</p>
                                <a href="{{ route('admin.managers.payments.create', $manager) }}" class="mt-4 inline-block btn-primary">Додати першу виплату</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4">
        @forelse($payments as $payment)
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-xs font-medium text-gray-500 mr-2">#{{ $payment->id }}</span>
                            <h3 class="text-lg font-bold text-green-600">{{ number_format($payment->amount, 2) }} zł</h3>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Дата виплати:</span>
                                <span class="font-medium">{{ $payment->payment_date->format('d.m.Y') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Період:</span>
                                <span class="font-medium">
                                    {{ $payment->period_from->format('d.m.Y') }} - {{ $payment->period_to->format('d.m.Y') }}
                                </span>
                            </div>
                            @if($payment->notes)
                                <div class="pt-2 border-t border-gray-200">
                                    <p class="text-sm text-gray-500">{{ $payment->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex gap-2 pt-3 border-t border-gray-200">
                    <a href="{{ route('admin.managers.payments.edit', [$manager, $payment]) }}" class="flex-1 btn-edit text-center py-2.5">Редагувати</a>
                    <form action="{{ route('admin.managers.payments.destroy', [$manager, $payment]) }}" method="POST" class="flex-1" onsubmit="return confirm('Ви впевнені, що хочете видалити цю виплату?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full btn-delete py-2.5">Видалити</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-sm text-gray-500">Виплати не знайдено</p>
                <a href="{{ route('admin.managers.payments.create', $manager) }}" class="mt-4 inline-block btn-primary">Додати першу виплату</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
