@extends('layouts.app')

@section('title', 'Редагувати виплату: ' . $manager->name)

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Редагувати виплату</h1>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">{{ $manager->name }} ({{ $manager->email }})</p>
            </div>
            <a href="{{ route('admin.managers.payments', $manager) }}" class="btn-secondary">
                ← Назад
            </a>
        </div>
    </div>

    <div class="card max-w-2xl">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.managers.payments.update', [$manager, $payment]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="amount">Сума виплати (zł)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0" value="{{ old('amount', $payment->amount) }}" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="payment_date">Дата виплати</label>
                <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="period_from">Період від</label>
                <input type="date" id="period_from" name="period_from" value="{{ old('period_from', $payment->period_from->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="period_to">Період до</label>
                <input type="date" id="period_to" name="period_to" value="{{ old('period_to', $payment->period_to->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="notes">Примітки (необов'язково)</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Додаткові примітки про виплату">{{ old('notes', $payment->notes) }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-4 flex-wrap mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Зберегти зміни
                </button>
                <a href="{{ route('admin.managers.payments', $manager) }}" class="btn-secondary flex-1 sm:flex-none text-center">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
