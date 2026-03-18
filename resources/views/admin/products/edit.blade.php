@extends('layouts.app')

@section('title', 'Редагувати товар')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Редагувати товар</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Оновіть інформацію про товар</p>
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

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Вкладка / блок "Змінити фото" --}}
            <div class="mb-8 p-6 rounded-2xl border-2 border-dashed border-blue-200 bg-blue-50/50">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Фото товару</h3>
                @if($product->image_path)
                    <div class="flex flex-wrap items-center gap-6 mb-4">
                        <img src="{{ $product->image_url }}" alt="" class="h-28 w-28 object-cover rounded-xl border-2 border-white shadow-md">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Поточне фото</p>
                            <p class="text-sm text-gray-500 mt-0.5">Оберіть новий файл нижче, щоб замінити</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-600 mb-4">Фото ще не додано. Оберіть файл нижче.</p>
                @endif
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Змінити фото</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white file:font-medium hover:file:bg-blue-700 file:cursor-pointer">
                <p class="mt-2 text-xs text-gray-500">До 5 МБ. Формати: JPEG, PNG, GIF, WebP. Якщо не завантажується — зменшіть розмір фото.</p>
            </div>

            <div class="form-group">
                <label for="name">Назва товару</label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="form-group">
                <label for="description">Опис товару</label>
                <textarea id="description" name="description" rows="4" placeholder="Короткий опис для картки товару на сайті">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="purchase_price">Ціна закупу (zł)</label>
                <input type="number" id="purchase_price" name="purchase_price" step="0.01" min="0" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                <p class="mt-1 text-sm text-gray-500">Собівартість — для розрахунку прибутку.</p>
            </div>

            <div class="form-group">
                <label for="sale_price">Ціна на сайті (zł)</label>
                <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price) }}" placeholder="Якщо порожньо — буде ціна закупу">
                <p class="mt-1 text-sm text-gray-500">Ціна, яка показується клієнту і за якою рахується дохід. Прибуток = ціна на сайті − ціна закупу.</p>
            </div>

            @if(\Illuminate\Support\Facades\Schema::hasColumn('products', 'quantity_ursynow'))
            <div class="form-group p-6 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Наявність по районах (самовивіз)</h3>
                <p class="text-sm text-gray-600 mb-4">Якщо без смаків — вкажіть кількість на Урсинові та Празі окремо. На сайті клієнт обирає спосіб доставки і район — показується наявність тільки для обраного району.</p>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="quantity_ursynow" class="block text-sm font-medium text-gray-700 mb-1">Урсинув</label>
                        <input type="number" id="quantity_ursynow" name="quantity_ursynow" min="0" value="{{ old('quantity_ursynow', $product->quantity_ursynow ?? 0) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                    </div>
                    <div>
                        <label for="quantity_praga" class="block text-sm font-medium text-gray-700 mb-1">Прага</label>
                        <input type="number" id="quantity_praga" name="quantity_praga" min="0" value="{{ old('quantity_praga', $product->quantity_praga ?? 0) }}" class="w-full rounded-xl border border-gray-300 px-4 py-2">
                    </div>
                    <div>
                        <label for="quantity_total" class="block text-sm font-medium text-gray-700 mb-1">Разом (InPost)</label>
                        @php $pt = (int)old('quantity_ursynow', $product->quantity_ursynow ?? 0) + (int)old('quantity_praga', $product->quantity_praga ?? 0); @endphp
                        <input type="number" id="quantity_total" min="0" value="{{ $pt }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 bg-gray-100" readonly>
                        <input type="hidden" name="quantity" id="quantity_hidden" value="{{ $pt }}">
                    </div>
                </div>
            </div>
            @else
            <div class="form-group">
                <label for="quantity">Кількість на складі (якщо без смаків)</label>
                <input type="number" id="quantity" name="quantity" min="0" value="{{ old('quantity', $product->quantity ?? 0) }}" placeholder="0">
            </div>
            @endif

            <div class="form-group p-6 rounded-2xl border-2 border-dashed border-amber-200 bg-amber-50/50">
                <h3 class="text-base font-semibold text-gray-900 mb-2">Смаки / асортимент</h3>
                <p class="text-sm text-gray-600 mb-4">Можна додати варіанти (смаки). Для кожного смаку — наявність на Урсинові та Празі окремо.</p>
                <div id="variants-list">
                    @php
                        $hasDistrict = \Illuminate\Support\Facades\Schema::hasColumn('product_variants', 'quantity_ursynow');
                        $variantsForForm = old('variants', $product->variants->map(fn($v) => [
                            'name' => $v->name,
                            'quantity' => $v->quantity,
                            'quantity_ursynow' => $hasDistrict ? ($v->quantity_ursynow ?? 0) : 0,
                            'quantity_praga' => $hasDistrict ? ($v->quantity_praga ?? 0) : 0,
                        ])->toArray());
                        if (empty($variantsForForm)) {
                            $variantsForForm = [['name' => '', 'quantity' => 0, 'quantity_ursynow' => 0, 'quantity_praga' => 0]];
                        }
                    @endphp
                    @foreach($variantsForForm as $idx => $v)
                        <div class="variant-row mb-4 p-3 bg-white rounded-lg border border-amber-100">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <input type="text" name="variants[{{ $idx }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="Смак" class="flex-1 min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg">
                                <button type="button" class="variant-remove px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">Видалити</button>
                            </div>
                            @if($hasDistrict)
                            <div class="grid grid-cols-3 gap-2 text-sm">
                                <div><label class="text-gray-500">Урсинув</label><input type="number" data-variant-qty-u name="variants[{{ $idx }}][quantity_ursynow]" value="{{ $v['quantity_ursynow'] ?? 0 }}" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg"></div>
                                <div><label class="text-gray-500">Прага</label><input type="number" data-variant-qty-p name="variants[{{ $idx }}][quantity_praga]" value="{{ $v['quantity_praga'] ?? 0 }}" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg"></div>
                                <div>
                                    <label class="text-gray-500">Разом (InPost)</label>
                                    @php $vt = (int)($v['quantity_ursynow'] ?? 0) + (int)($v['quantity_praga'] ?? 0); @endphp
                                    <input type="number" data-variant-qty-t value="{{ $vt }}" min="0" class="w-full px-2 py-1.5 border border-gray-200 rounded-lg bg-gray-100" readonly>
                                    <input type="hidden" data-variant-qty-hidden name="variants[{{ $idx }}][quantity]" value="{{ $vt }}">
                                </div>
                            </div>
                            @else
                            <input type="number" name="variants[{{ $idx }}][quantity]" value="{{ $v['quantity'] ?? 0 }}" min="0" placeholder="К-сть" class="w-24 px-2 py-1.5 border border-gray-300 rounded-lg">
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="button" id="variant-add" class="mt-2 px-4 py-2 bg-amber-100 text-amber-800 rounded-lg text-sm font-medium hover:bg-amber-200">+ Додати смак</button>
            </div>

            <div class="form-group">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="available_in_bot" value="0">
                    <input type="checkbox" name="available_in_bot" value="1" {{ old('available_in_bot', $product->available_in_bot) ? 'checked' : '' }}>
                    <span>Доступний у боті / на сайті (shop_site)</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">Якщо увімкнено, товар показується на сайті магазину.</p>
            </div>

            <div class="form-group">
                <label for="shop_category">Категорія на сайті</label>
                <select id="shop_category" name="shop_category">
                    <option value="">— не показувати в категоріях —</option>
                    @foreach($shopCategories ?? [] as $cat)
                        <option value="{{ $cat->slug }}" {{ old('shop_category', $product->shop_category) === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="manager_id">Менеджер</label>
                <select id="manager_id" name="manager_id">
                    <option value="">Для всіх (без призначення)</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id', $product->manager_id) == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }} ({{ $manager->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-gray-500">Якщо не вибрано менеджера, товар буде доступний всім менеджерам</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-4 flex-wrap mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Оновити товар
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary flex-1 sm:flex-none text-center">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(function() {
    var list = document.getElementById('variants-list');
    var addBtn = document.getElementById('variant-add');
    if (!list || !addBtn) return;
    var idx = list.querySelectorAll('.variant-row').length;
    var hasDistrict = list.querySelector('input[name*="quantity_ursynow"]') !== null;

    function toInt(v) { var n = parseInt(String(v || '0'), 10); return isNaN(n) ? 0 : n; }
    function recalcProductTotal() {
        var u = document.getElementById('quantity_ursynow');
        var p = document.getElementById('quantity_praga');
        var t = document.getElementById('quantity_total');
        var h = document.getElementById('quantity_hidden');
        if (!u || !p || !t || !h) return;
        var total = toInt(u.value) + toInt(p.value);
        t.value = total;
        h.value = total;
    }
    function recalcVariantRow(row) {
        if (!row) return;
        var u = row.querySelector('[data-variant-qty-u]');
        var p = row.querySelector('[data-variant-qty-p]');
        var t = row.querySelector('[data-variant-qty-t]');
        var h = row.querySelector('[data-variant-qty-hidden]');
        if (!u || !p || !t || !h) return;
        var total = toInt(u.value) + toInt(p.value);
        t.value = total;
        h.value = total;
    }
    function attachVariantListeners(row) {
        if (!row) return;
        row.querySelectorAll('[data-variant-qty-u],[data-variant-qty-p]').forEach(function(inp) {
            inp.addEventListener('input', function() { recalcVariantRow(row); });
        });
    }

    document.getElementById('quantity_ursynow')?.addEventListener('input', recalcProductTotal);
    document.getElementById('quantity_praga')?.addEventListener('input', recalcProductTotal);
    recalcProductTotal();
    addBtn.addEventListener('click', function() {
        var row = document.createElement('div');
        row.className = 'variant-row mb-4 p-3 bg-white rounded-lg border border-amber-100';
        if (hasDistrict) {
            row.innerHTML = '<div class="flex flex-wrap items-center gap-2 mb-2"><input type="text" name="variants[' + idx + '][name]" value="" placeholder="Смак" class="flex-1 min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg"><button type="button" class="variant-remove px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">Видалити</button></div><div class="grid grid-cols-3 gap-2 text-sm"><div><label class="text-gray-500">Урсинув</label><input type="number" data-variant-qty-u name="variants[' + idx + '][quantity_ursynow]" value="0" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg"></div><div><label class="text-gray-500">Прага</label><input type="number" data-variant-qty-p name="variants[' + idx + '][quantity_praga]" value="0" min="0" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg"></div><div><label class="text-gray-500">Разом (InPost)</label><input type="number" data-variant-qty-t value="0" min="0" class="w-full px-2 py-1.5 border border-gray-200 rounded-lg bg-gray-100" readonly><input type="hidden" data-variant-qty-hidden name="variants[' + idx + '][quantity]" value="0"></div></div>';
        } else {
            row.innerHTML = '<div class="flex flex-wrap items-center gap-2 mb-2"><input type="text" name="variants[' + idx + '][name]" value="" placeholder="Смак" class="flex-1 min-w-[120px] px-3 py-2 border border-gray-300 rounded-lg"><input type="number" name="variants[' + idx + '][quantity]" value="0" min="0" class="w-24 px-2 py-1.5 border border-gray-300 rounded-lg"><button type="button" class="variant-remove px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm">Видалити</button></div>';
        }
        list.appendChild(row);
        idx++;
        if (hasDistrict) {
            attachVariantListeners(row);
            recalcVariantRow(row);
        }
        row.querySelector('.variant-remove').addEventListener('click', function() { row.remove(); });
    });
    list.querySelectorAll('.variant-remove').forEach(function(btn) {
        btn.addEventListener('click', function() { btn.closest('.variant-row').remove(); });
    });

    if (hasDistrict) {
        list.querySelectorAll('.variant-row').forEach(function(r) { attachVariantListeners(r); recalcVariantRow(r); });
    }
})();
</script>
@endpush
@endsection
