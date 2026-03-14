@extends('layouts.app')

@section('title', 'Перегляд товару')

@section('content')
<div class="px-4 sm:px-0">
    <div class="card max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Перегляд товару</h1>

        <div class="space-y-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Назва</label>
                <p class="text-gray-900">{{ $product->name }}</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700">Ціна закупівлі</label>
                <p class="text-gray-900">{{ number_format($product->purchase_price ?? 0, 2) }} zł</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700">Кількість на складі</label>
                <p class="text-gray-900">{{ $product->quantity ?? 0 }}</p>
            </div>

            @if($product->description)
            <div>
                <label class="text-sm font-semibold text-gray-700">Опис</label>
                <p class="text-gray-900">{{ $product->description }}</p>
            </div>
            @endif
        </div>

        <div class="flex gap-4 flex-wrap mt-8">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary">
                Редагувати
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                Назад
            </a>
        </div>
    </div>
</div>
@endsection

