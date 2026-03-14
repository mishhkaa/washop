@extends('layouts.app')

@section('title', 'Товари')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Товари</h1>
            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Управління товарами</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn-primary w-full sm:w-auto">
            + Додати товар
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

    <!-- Фільтри -->
    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Фільтри</h2>
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="form-group">
                <label for="search">Пошук по назві</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Введіть назву товару">
            </div>

            <div class="form-group">
                <label for="manager_id">Менеджер</label>
                <select id="manager_id" name="manager_id">
                    <option value="">Всі товари</option>
                    <option value="null" {{ request('manager_id') == 'null' ? 'selected' : '' }}>Без менеджера</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ request('manager_id') == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="min_quantity">Мін. кількість</label>
                <input type="number" id="min_quantity" name="min_quantity" value="{{ request('min_quantity') }}" min="0" placeholder="0">
            </div>

            <div class="form-group">
                <label for="max_quantity">Макс. кількість</label>
                <input type="number" id="max_quantity" name="max_quantity" value="{{ request('max_quantity') }}" min="0" placeholder="Без обмежень">
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                <button type="submit" class="btn-primary">Фільтрувати</button>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">Очистити</a>
            </div>
        </form>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ціна закупівлі</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Менеджер</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $product->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ number_format($product->purchase_price ?? 0, 2) }} zł</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if($product->manager)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $product->manager->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-edit">Редагувати</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Ви впевнені?')" class="inline-block">
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
                                <p class="text-sm text-gray-500">Товари не знайдено</p>
                                <a href="{{ route('admin.products.create') }}" class="mt-4 inline-block btn-primary">Додати перший товар</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4">
        @forelse($products as $product)
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-xs font-medium text-gray-500 mr-2">#{{ $product->id }}</span>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Ціна закупівлі:</span>
                                <span class="font-bold text-green-600">{{ number_format($product->purchase_price ?? 0, 2) }} zł</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Кількість на складі:</span>
                                <span class="font-medium">{{ $product->quantity ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm items-center">
                                <span class="text-gray-500">Менеджер:</span>
                                <span class="font-medium">
                                    @if($product->manager)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $product->manager->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 mt-3">
                    <a href="{{ route('admin.products.edit', $product) }}" class="flex-1 btn-edit text-center py-2.5">Редагувати</a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Ви впевнені?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full btn-delete py-2.5">Видалити</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-sm text-gray-500">Товари не знайдено</p>
                <a href="{{ route('admin.products.create') }}" class="mt-4 inline-block btn-primary">Додати перший товар</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
