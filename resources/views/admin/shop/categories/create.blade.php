@extends('layouts.app')

@section('title', 'Додати категорію магазину')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Додати категорію</h1>
        <p class="mt-1 text-gray-600">Назва, slug (для фільтра на сайті) та фото.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.shop.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Блок "Фото категорії" --}}
            <div class="mb-8 p-6 rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/50">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Фото категорії</h3>
                <p class="text-sm text-gray-600 mb-4">JPEG, PNG, GIF або WebP, до 5 МБ. Якщо не завантажується — зменшіть розмір фото.</p>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Оберіть зображення</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white file:font-medium hover:file:bg-blue-700 file:cursor-pointer">
            </div>

            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Назва</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (латиниця, без пробілів)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}" pattern="[a-z0-9_-]+" placeholder="napryklad-pidsystemy" required class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono">
                    <p class="mt-1 text-xs text-gray-500">Використовується в URL та для прив’язки товарів (наприклад: pods, liquids).</p>
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Порядок (число)</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                    Зберегти
                </button>
                <a href="{{ route('admin.shop.categories.index') }}" class="px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50">Скасувати</a>
            </div>
        </form>
    </div>

    <p class="mt-6">
        <a href="{{ route('admin.bot.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← До Магазину</a>
    </p>
</div>
@endsection
