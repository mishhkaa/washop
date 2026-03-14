@extends('layouts.app')

@section('title', 'Мої товари')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Мої товари</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Товари, призначені вам або доступні всім</p>
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

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Назва</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Кількість</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('manager.products.show', $product) }}'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $product->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($product->manager_id === auth()->id())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Призначено мені
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Для всіх
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">Товари не знайдено</p>
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
            <div class="card cursor-pointer" onclick="window.location='{{ route('manager.products.show', $product) }}'">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center mb-2">
                            <span class="text-xs font-medium text-gray-500 mr-2">#{{ $product->id }}</span>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h3>
                        </div>
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Кількість на складі:</span>
                                <span class="font-medium">{{ $product->quantity ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between text-sm items-center mt-2">
                                <span class="text-gray-500">Статус:</span>
                                <span class="font-medium">
                                    @if($product->manager_id === auth()->id())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Призначено мені
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Для всіх
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-sm text-gray-500">Товари не знайдено</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

