@extends('layouts.app')

@section('title', 'Категорії магазину')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Категорії магазину</h1>
            <p class="mt-1 text-gray-600">Відображаються на головній сайту. Додайте фото та slug.</p>
        </div>
        <a href="{{ route('admin.shop.categories.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-xl transition-all">
            + Додати категорію
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Фото</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Назва</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Порядок</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase">Дії</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($cat->image_path)
                                <img src="{{ $cat->image_url }}" alt="" class="h-16 w-16 rounded-xl object-cover border border-gray-200">
                            @else
                                <div class="h-16 w-16 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-xs">Немає фото</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->name }}</td>
                        <td class="px-6 py-4 text-gray-500 font-mono text-sm">{{ $cat->slug }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $cat->sort_order }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('admin.shop.categories.edit', $cat) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Редагувати</a>
                            <form action="{{ route('admin.shop.categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Видалити категорію?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Видалити</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <p class="mb-4">Категорій ще немає.</p>
                            <a href="{{ route('admin.shop.categories.create') }}" class="text-blue-600 font-medium hover:underline">Додати категорію</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-6">
        <a href="{{ route('admin.bot.index', ['tab' => 'categories']) }}" class="text-gray-500 hover:text-gray-700 text-sm">← Повернутися до Магазину</a>
    </p>
</div>
@endsection
